<?php
session_start();
require_once __DIR__ . '/../../config/database_config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Usuario no autenticado.';
    echo json_encode($response);
    exit;
}

$userId = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$notificationId = $input['notification_id'] ?? null;
$routineId = $input['routine_id'] ?? null;

if (!$notificationId || !$routineId) {
    $response['message'] = 'Faltan datos para completar la acción.';
    echo json_encode($response);
    exit;
}

try {
    // 1. Verificar que la notificación pertenece al usuario
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM notificaciones WHERE id_notificacion = ? AND id_usuario = ?");
    $stmt->execute([$notificationId, $userId]);
    $notification = $stmt->fetch();

    if (!$notification) {
        $response['message'] = 'Notificación no encontrada o no autorizada.';
        echo json_encode($response);
        exit;
    }

    // 2. Marcar la rutina como completada para hoy
    $success = markRoutineComplete($routineId);

    if ($success) {
        // 3. Opcional: Marcar la notificación como leída
        markNotificationRead($notificationId);
        $response['success'] = true;
        $response['message'] = 'Rutina marcada como completada.';
    } else {
        $response['message'] = 'La rutina ya estaba completada hoy o hubo un error.';
    }

} catch (Exception $e) {
    logError($e->getMessage(), __FILE__, __LINE__);
    $response['message'] = 'Error del servidor: ' . $e->getMessage();
}

echo json_encode($response);
?>
