<?php
/**
 * PetDay - Página de Calendario
 */

session_start();
require_once '../../config/database_config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$user = getUserById($userId);
$pets = getUserPets($userId);

// Variables para el header.php
$isLoggedIn = true;

// Lógica para el calendario
$currentMonth = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$currentYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$currentDay = isset($_GET['day']) ? intval($_GET['day']) : date('j');
$currentView = isset($_GET['view']) ? $_GET['view'] : 'month';

$monthNames = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

// Obtener todas las rutinas y eventos para el mes actual
$allRoutines = [];
$allEvents = [];
foreach ($pets as $pet) {
    $petRoutines = getPetRoutines($pet['id_mascota']);
    foreach ($petRoutines as $routine) {
        $allRoutines[] = array_merge($routine, ['pet_name' => $pet['nombre'], 'pet_id' => $pet['id_mascota']]);
    }
    $petEvents = getUpcomingEvents($pet['id_mascota'], 365); // Obtener eventos para el año
    foreach ($petEvents as $event) {
        $allEvents[] = array_merge($event, ['pet_name' => $pet['nombre'], 'pet_id' => $pet['id_mascota']]);
    }
}

// Función auxiliar para generar enlaces de navegación
function generateCalendarNavUrl($month, $year, $view, $day = null) {
    $url = "?month=$month&year=$year&view=$view";
    if ($day !== null) {
        $url .= "&day=$day";
    }
    return $url;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario - PetDay</title>
    <link rel="stylesheet" href="../../css/style.css?v=1.12">
    <link rel="icon" href="../../images/favicon.png" type="image/png">
</head>
<body>
    <?php include_once __DIR__ . '/../includes/header.php'; ?>

    <main class="main-content">
        <section class="calendar-page">
            <div class="container">
                <div class="page-header">
                    <h2>Calendario de Actividades</h2>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <select id="calendarViewSelector" class="form-control form-select" style="width: auto;">
                            <option value="month">Mes</option>
                            <option value="week">Semana</option>
                            <option value="day">Día</option>
                        </select>
                        <a href="../events/create_event.php" class="btn btn-sm btn-primary">+ Nuevo Evento</a>
                    </div>
                </div>

                <div class="calendar-nav">
                    <?php 
                    if ($currentView === 'month') {
                        $prevMonth = $currentMonth - 1;
                        $prevYear = $currentYear;
                        if ($prevMonth < 1) {
                            $prevMonth = 12;
                            $prevYear--;
                        }
                        $nextMonth = $currentMonth + 1;
                        $nextYear = $currentYear;
                        if ($nextMonth > 12) {
                            $nextMonth = 1;
                            $nextYear++;
                        }
                        echo '<a href="' . generateCalendarNavUrl($prevMonth, $prevYear, $currentView) . '" class="btn btn-sm btn-outline">&lt; Anterior</a>';
                        echo '<h3>' . $monthNames[$currentMonth] . ' ' . $currentYear . '</h3>';
                        echo '<a href="' . generateCalendarNavUrl($nextMonth, $nextYear, $currentView) . '" class="btn btn-sm btn-outline">Siguiente &gt;</a>';
                    } elseif ($currentView === 'week') {
                        $date = new DateTime("$currentYear-$currentMonth-$currentDay");
                        $date->modify('monday this week');
                        $prevWeek = clone $date;
                        $prevWeek->modify('-1 week');
                        $nextWeek = clone $date;
                        $nextWeek->modify('+1 week');

                        echo '<a href="' . generateCalendarNavUrl($prevWeek->format('n'), $prevWeek->format('Y'), $currentView, $prevWeek->format('j')) . '" class="btn btn-sm btn-outline">&lt; Semana Anterior</a>';
                        echo '<h3>Semana del ' . $date->format('d M Y') . '</h3>';
                        echo '<a href="' . generateCalendarNavUrl($nextWeek->format('n'), $nextWeek->format('Y'), $currentView, $nextWeek->format('j')) . '" class="btn btn-sm btn-outline">Semana Siguiente &gt;</a>';
                    }
                    ?>
                </div>

                <?php if ($currentView === 'month'): ?>
                    <div class="calendar-grid">
                        <div class="calendar-day-header"><span class="day-full">Lunes</span><span class="day-abbr">L</span></div>
                        <div class="calendar-day-header"><span class="day-full">Martes</span><span class="day-abbr">M</span></div>
                        <div class="calendar-day-header"><span class="day-full">Miércoles</span><span class="day-abbr">X</span></div>
                        <div class="calendar-day-header"><span class="day-full">Jueves</span><span class="day-abbr">J</span></div>
                        <div class="calendar-day-header"><span class="day-full">Viernes</span><span class="day-abbr">V</span></div>
                        <div class="calendar-day-header"><span class="day-full">Sábado</span><span class="day-abbr">S</span></div>
                        <div class="calendar-day-header"><span class="day-full">Domingo</span><span class="day-abbr">D</span></div>

                        <?php 
                        $date = new DateTime("$currentYear-$currentMonth-01");
                        $daysInMonth = $date->format('t');
                        $firstDayOfWeek = $date->format('N');
                        $calendar = [];
                        for ($i = 1; $i < $firstDayOfWeek; $i++) { $calendar[] = null; }
                        for ($dayCounter = 1; $dayCounter <= $daysInMonth; $dayCounter++) {
                            $currentDate = sprintf("%04d-%02d-%02d", $currentYear, $currentMonth, $dayCounter);
                            $calendar[] = ['date' => $currentDate, 'day' => $dayCounter, 'routines' => [], 'events' => []];
                        }
                        foreach ($calendar as &$dayData) {
                            if ($dayData === null) continue;
                            $dateStr = $dayData['date'];
                            $dayOfWeek = strtolower(date('l', strtotime($dateStr)));
                            $dayInSpanish = ['monday' => 'lunes', 'tuesday' => 'martes', 'wednesday' => 'miercoles', 'thursday' => 'jueves', 'friday' => 'viernes', 'saturday' => 'sabado', 'sunday' => 'domingo'][$dayOfWeek];
                            foreach ($allRoutines as $routine) {
                                $diasSemana = explode(',', $routine['dias_semana']);
                                if (in_array($dayInSpanish, $diasSemana)) { $dayData['routines'][] = $routine; }
                            }
                            foreach ($allEvents as $event) {
                                if (date('Y-m-d', strtotime($event['fecha_evento'])) == $dateStr) { $dayData['events'][] = $event; }
                            }
                        }
                        unset($dayData);
                        ?>

                        <?php foreach ($calendar as $dayData): ?>
                            <?php if ($dayData === null): ?>
                                <div class="calendar-day empty"></div>
                            <?php else: ?>
                                <div class="calendar-day <?php echo (date('Y-m-d') == $dayData['date']) ? 'today' : ''; ?>" 
                                     data-date="<?php echo $dayData['date']; ?>"
                                     data-events='<?php echo htmlspecialchars(json_encode(array_merge($dayData['routines'], $dayData['events'])), ENT_QUOTES, 'UTF-8'); ?>'>
                                    <span class="day-number"><?php echo $dayData['day']; ?></span>
                                    <div class="day-events">
                                        <?php $colorIndex = 1; ?>
                                        <?php foreach ($dayData['routines'] as $routine): ?>
                                            <div class="event-item routine-event routine-bg-<?php echo $colorIndex; ?>">
                                                <span class="event-icon"><?php echo getActivityIcon($routine['tipo_actividad']); ?></span>
                                                <span class="event-time"><?php echo date('H:i', strtotime($routine['hora_programada'])); ?></span>
                                                <span class="event-title"><?php echo htmlspecialchars($routine['nombre_actividad']); ?> (<?php echo htmlspecialchars($routine['pet_name']); ?>)</span>
                                            </div>
                                            <?php $colorIndex = ($colorIndex % 5) + 1; ?>
                                        <?php endforeach; ?>
                                        <?php foreach ($dayData['events'] as $event): ?>
                                            <div class="event-item calendar-event">
                                                <span class="event-icon">🏥</span>
                                                <span class="event-time"><?php echo date('H:i', strtotime($event['fecha_evento'])); ?></span>
                                                <span class="event-title"><?php echo htmlspecialchars($event['titulo']); ?> (<?php echo htmlspecialchars($event['pet_name']); ?>)</span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($currentView === 'week'): ?>
                    <?php
                    $date = new DateTime("$currentYear-$currentMonth-$currentDay");
                    $startOfWeek = clone $date;
                    $startOfWeek->modify('monday this week');
                    $endOfWeek = clone $startOfWeek;
                    $endOfWeek->modify('+6 days');
                    $weekDays = [];
                    $interval = new DateInterval('P1D');
                    $period = new DatePeriod($startOfWeek, $interval, $endOfWeek->modify('+1 day'));
                    foreach ($period as $day) {
                        $dayData = ['date' => $day->format('Y-m-d'), 'day_name' => $monthNames[$day->format('n')] . ' ' . $day->format('j'), 'routines' => [], 'events' => []];
                        $dayOfWeek = strtolower($day->format('l'));
                        $dayInSpanish = ['monday' => 'lunes', 'tuesday' => 'martes', 'wednesday' => 'miercoles', 'thursday' => 'jueves', 'friday' => 'viernes', 'saturday' => 'sabado', 'sunday' => 'domingo'][$dayOfWeek];
                        foreach ($allRoutines as $routine) {
                            $diasSemana = explode(',', $routine['dias_semana']);
                            if (in_array($dayInSpanish, $diasSemana)) { $dayData['routines'][] = $routine; }
                        }
                        foreach ($allEvents as $event) {
                            if (date('Y-m-d', strtotime($event['fecha_evento'])) == $day->format('Y-m-d')) { $dayData['events'][] = $event; }
                        }
                        $weekDays[] = $dayData;
                    }
                    ?>
                    <div class="calendar-week-grid">
                        <?php foreach ($weekDays as $dayData): ?>
                            <div class="calendar-day-week-view <?php echo (date('Y-m-d') == $dayData['date']) ? 'today' : ''; ?>" data-date="<?php echo $dayData['date']; ?>" data-events='<?php echo htmlspecialchars(json_encode(array_merge($dayData['routines'], $dayData['events'])), ENT_QUOTES, 'UTF-8'); ?>'>
                                <span class="day-number"><?php echo $dayData['day_name']; ?></span>
                                <div class="day-events">
                                    <?php foreach ($dayData['routines'] as $routine): ?>
                                        <div class="event-item routine-event">
                                            <span class="event-icon"><?php echo getActivityIcon($routine['tipo_actividad']); ?></span>
                                            <span class="event-time"><?php echo date('H:i', strtotime($routine['hora_programada'])); ?></span>
                                            <span class="event-title"><?php echo htmlspecialchars($routine['nombre_actividad']); ?> (<?php echo htmlspecialchars($routine['pet_name']); ?>)</span>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php foreach ($dayData['events'] as $event): ?>
                                        <div class="event-item calendar-event">
                                            <span class="event-icon">🏥</span>
                                            <span class="event-time"><?php echo date('H:i', strtotime($event['fecha_evento'])); ?></span>
                                            <span class="event-title"><?php echo htmlspecialchars($event['titulo']); ?> (<?php echo htmlspecialchars($event['pet_name']); ?>)</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($currentView === 'day'): ?>
                    <?php
                    $date = new DateTime("$currentYear-$currentMonth-$currentDay");
                    $dayData = ['date' => $date->format('Y-m-d'), 'day_name' => $monthNames[$date->format('n')] . ' ' . $date->format('j') . ', ' . $date->format('Y'), 'routines' => [], 'events' => []];
                    $dayOfWeek = strtolower($date->format('l'));
                    $dayInSpanish = ['monday' => 'lunes', 'tuesday' => 'martes', 'wednesday' => 'miercoles', 'thursday' => 'jueves', 'friday' => 'viernes', 'saturday' => 'sabado', 'sunday' => 'domingo'][$dayOfWeek];
                    foreach ($allRoutines as $routine) {
                        $diasSemana = explode(',', $routine['dias_semana']);
                        if (in_array($dayInSpanish, $diasSemana)) { $dayData['routines'][] = $routine; }
                    }
                    foreach ($allEvents as $event) {
                        if (date('Y-m-d', strtotime($event['fecha_evento'])) == $date->format('Y-m-d')) { $dayData['events'][] = $event; }
                    }
                    ?>
                    <div class="calendar-day-view">
                        <div class="calendar-day-single-view <?php echo (date('Y-m-d') == $dayData['date']) ? 'today' : ''; ?>" data-date="<?php echo $dayData['date']; ?>" data-events='<?php echo htmlspecialchars(json_encode(array_merge($dayData['routines'], $dayData['events'])), ENT_QUOTES, 'UTF-8'); ?>'>
                            <span class="day-number">Eventos para <?php echo $dayData['day_name']; ?></span>
                            <div class="day-events">
                                <?php if (empty($dayData['routines']) && empty($dayData['events'])): ?>
                                    <p class="text-muted">No hay eventos ni rutinas para este día.</p>
                                <?php else: ?>
                                    <?php foreach ($dayData['routines'] as $routine): ?>
                                        <div class="event-item routine-event">
                                            <span class="event-icon"><?php echo getActivityIcon($routine['tipo_actividad']); ?></span>
                                            <span class="event-time"><?php echo date('H:i', strtotime($routine['hora_programada'])); ?></span>
                                            <span class="event-title"><?php echo htmlspecialchars($routine['nombre_actividad']); ?> (<?php echo htmlspecialchars($routine['pet_name']); ?>)</span>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php foreach ($dayData['events'] as $event): ?>
                                        <div class="event-item calendar-event">
                                            <span class="event-icon">🏥</span>
                                            <span class="event-time"><?php echo date('H:i', strtotime($event['fecha_evento'])); ?></span>
                                            <span class="event-title"><?php echo htmlspecialchars($event['titulo']); ?> (<?php echo htmlspecialchars($event['pet_name']); ?>)</span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                
            </div>
        </section>
    </main>

    <?php include_once __DIR__ . '/../includes/footer.php'; ?>
    <script src="../../js/app.js?v=1.5"></script>

    <!-- Modal para detalles del día -->
    <div id="dayDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalDayTitle"></h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body" id="modalEventsList">
                <!-- Los eventos se cargarán aquí con JS -->
            </div>
            <div class="modal-footer" style="text-align: center;">
                <button id="editDayButton" class="btn btn-primary">Editar</button>
            </div>
        </div>
    </div>
</body>
</html>
