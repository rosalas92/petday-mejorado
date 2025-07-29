<?php
session_start();
require_once __DIR__ . '/../../config/database_config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Usuario no autenticado.';
    echo json_encode($response);
    exit;
}

$userId = $_SESSION['user_id'];
$notificationId = filter_input(INPUT_POST, 'notification_id', FILTER_VALIDATE_INT);

if (!$notificationId) {
    $response['message'] = 'ID de notificación inválido.';
    echo json_encode($response);
    exit;
}

try {
    $pdo = getDbConnection();

    // Marcar la notificación como leída, asegurándose de que pertenezca al usuario actual
    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = 1 WHERE id_notificacion = :notification_id AND id_usuario = :user_id");
    $stmt->bindParam(':notification_id', $notificationId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $response['success'] = true;
        $response['message'] = 'Notificación marcada como leída.';
    } else {
        $response['message'] = 'No se pudo marcar la notificación como leída o no pertenece a este usuario.';
    }

} catch (PDOException $e) {
    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = 'Error inesperado: ' . $e->getMessage();
}

echo json_encode($response);
?>