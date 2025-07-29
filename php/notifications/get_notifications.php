<?php
session_start();
require_once __DIR__ . '/../../config/database_config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'notifications' => [], 'unread_count' => 0];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Usuario no autenticado.';
    echo json_encode($response);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $pdo = getConnection();

    // Obtener notificaciones para el usuario, ordenadas por fecha descendente
    $stmt = $pdo->prepare("SELECT id_notificacion, titulo, mensaje, leida, fecha_envio FROM notificaciones WHERE id_usuario = :user_id ORDER BY fecha_envio DESC LIMIT 10");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener el conteo de notificaciones no leídas
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario = :user_id AND leida = 0");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $unreadCount = $stmt->fetchColumn();

    $response['success'] = true;
    $response['notifications'] = $notifications;
    $response['unread_count'] = $unreadCount;

} catch (PDOException $e) {
    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = 'Error inesperado: ' . $e->getMessage();
}

echo json_encode($response);
?>