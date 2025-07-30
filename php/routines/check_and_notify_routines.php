<?php
require_once __DIR__ . '/../../config/database_config.php';
require_once __DIR__ . '/../includes/functions.php';

// Este script está diseñado para ser ejecutado por un cron job o tarea programada.
// No debe ser accesible directamente desde el navegador.
if (php_sapi_name() !== 'cli') {
    // Opcional: Redirigir o mostrar un error si se accede vía web
    // header('Location: /');
    // exit;
}

try {
    $pdo = getConnection();

    // Obtener el día de la semana actual en español
    $dayOfWeek = strtolower(date('l'));
    $dayInSpanish = [
        'monday' => 'lunes',
        'tuesday' => 'martes',
        'wednesday' => 'miercoles',
        'thursday' => 'jueves',
        'friday' => 'viernes',
        'saturday' => 'sabado',
        'sunday' => 'domingo'
    ][$dayOfWeek];

    // Obtener todas las rutinas activas programadas para hoy
    $stmt = $pdo->prepare("
        SELECT 
            r.id_rutina, 
            r.nombre_actividad, 
            r.hora_programada, 
            m.id_mascota, 
            m.nombre AS nombre_mascota, 
            u.id_usuario
        FROM rutinas r
        JOIN mascotas m ON r.id_mascota = m.id_mascota
        JOIN usuarios u ON m.id_usuario = u.id_usuario
        WHERE r.activa = 1 
        AND FIND_IN_SET(:dayInSpanish, r.dias_semana)
    ");
    $stmt->execute(['dayInSpanish' => $dayInSpanish]);
    $routines = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $currentDateTime = new DateTime();

    foreach ($routines as $routine) {
        $routineTime = new DateTime($routine['hora_programada']);
        $routineDateTime = new DateTime($currentDateTime->format('Y-m-d') . ' ' . $routineTime->format('H:i:s'));

        // Solo enviar notificación si la hora programada ya pasó o es la hora actual
        if ($currentDateTime >= $routineDateTime) {
            // Verificar si ya se envió una notificación para esta rutina hoy
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM notificaciones 
                WHERE id_usuario = :id_usuario 
                AND tipo = 'rutina' 
                AND id_entidad_relacionada = :id_rutina 
                AND DATE(fecha_envio) = CURDATE()
            ");
            $stmt->execute([
                'id_usuario' => $routine['id_usuario'],
                'id_rutina' => $routine['id_rutina']
            ]);
            $notificationSentToday = $stmt->fetchColumn();

            if ($notificationSentToday == 0) {
                // Verificar si la rutina ya fue completada hoy
                if (!isRoutineCompletedToday($routine['id_rutina'])) {
                    $title = "¡Hora de la rutina de " . htmlspecialchars($routine['nombre_mascota']) . "!";
                    $message = "Es hora de la rutina de " . htmlspecialchars($routine['nombre_actividad']) . " para " . htmlspecialchars($routine['nombre_mascota']) . ".";
                    
                    sendNotification(
                        $routine['id_usuario'], 
                        $title, 
                        $message, 
                        'rutina', 
                        $routine['id_rutina']
                    );
                    error_log("Notificación enviada para rutina ID: " . $routine['id_rutina'] . " para usuario ID: " . $routine['id_usuario']);
                }
            }
        }
    }

} catch (PDOException $e) {
    error_log("Error de base de datos en check_and_notify_routines.php: " . $e->getMessage());
} catch (Exception $e) {
    error_log("Error general en check_and_notify_routines.php: " . $e->getMessage());
}
?>