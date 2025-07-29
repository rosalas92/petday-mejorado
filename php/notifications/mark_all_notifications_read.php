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

try {
    $pdo = getConnection();

    // Marcar todas las notificaciones no leídas del usuario como leídas
    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = 1 WHERE id_usuario = :user_id AND leida = 0");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $response['success'] = true;
    $response['message'] = 'Todas las notificaciones marcadas como leídas.';

} catch (PDOException $e) {
    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = 'Error inesperado: ' . $e->getMessage();
}

echo json_encode($response);
?>