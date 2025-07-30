<?php
session_start();
require_once __DIR__ . '/../../config/database_config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'notifications_created' => 0];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Usuario no autenticado.';
    echo json_encode($response);
    exit;
}

$userId = $_SESSION['user_id'];
$currentDateTime = new DateTime();
$tenMinutesLater = (clone $currentDateTime)->modify('+10 minutes');

// Obtener el día de la semana actual en español
$dayOfWeek = strtolower($currentDateTime->format('l'));
$dayInSpanish = [
    'monday' => 'lunes',
    'tuesday' => 'martes',
    'wednesday' => 'miercoles',
    'thursday' => 'jueves',
    'friday' => 'viernes',
    'saturday' => 'sabado',
    'sunday' => 'domingo'
][$dayOfWeek];

try {
    $pdo = getConnection();

    // Obtener rutinas activas del usuario para hoy, cuya hora programada esté en los próximos 10 minutos
    $stmt = $pdo->prepare("
        SELECT 
            r.id_rutina, 
            r.nombre_actividad, 
            r.hora_programada, 
            m.nombre AS nombre_mascota
        FROM rutinas r
        JOIN mascotas m ON r.id_mascota = m.id_mascota
        WHERE m.id_usuario = :userId 
        AND r.activa = 1 
        AND FIND_IN_SET(:dayInSpanish, r.dias_semana)
        AND r.hora_programada BETWEEN :currentTime AND :tenMinutesLater
    ");

    $stmt->execute([
        ':userId' => $userId,
        ':dayInSpanish' => $dayInSpanish,
        ':currentTime' => $currentDateTime->format('H:i:s'),
        ':tenMinutesLater' => $tenMinutesLater->format('H:i:s')
    ]);
    $upcomingRoutines = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($upcomingRoutines as $routine) {
        // Verificar si ya se envió una notificación para esta rutina hoy
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM notificaciones 
            WHERE id_usuario = :userId 
            AND tipo = 'rutina' 
            AND id_entidad_relacionada = :routineId 
            AND DATE(fecha_envio) = CURDATE()
        ");
        $stmt->execute([
            ':userId' => $userId,
            ':routineId' => $routine['id_rutina']
        ]);
        $notificationSentToday = $stmt->fetchColumn();

        if ($notificationSentToday == 0) {
            $title = "¡Rutina próxima para " . htmlspecialchars($routine['nombre_mascota']) . "!";
            $message = "La rutina de " . htmlspecialchars($routine['nombre_actividad']) . " para " . htmlspecialchars($routine['nombre_mascota']) . " es a las " . date('H:i', strtotime($routine['hora_programada'])) . ".";
            
            if (sendNotification($userId, $title, $message, 'rutina', $routine['id_rutina'])) {
                $response['notifications_created']++;
            }
        }
    }

    $response['success'] = true;
    $response['message'] = 'Verificación de rutinas próximas completada.';

} catch (PDOException $e) {
    error_log("Error de base de datos en check_upcoming_routines.php: " . $e->getMessage());
    $response['message'] = 'Error de base de datos.';
} catch (Exception $e) {
    error_log("Error general en check_upcoming_routines.php: " . $e->getMessage());
    $response['message'] = 'Error interno del servidor.';
}

echo json_encode($response);
?>