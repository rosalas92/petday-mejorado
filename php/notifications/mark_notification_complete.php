<?php
session_start();
require_once '../../config/database_config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['message' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $notificationId = $data['id'];

    $sql = "UPDATE notificaciones SET leida = 1 WHERE id_notificacion = ? AND id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $notificationId, $_SESSION['user_id']);

    if (empty($notificationId)) {
        http_response_code(400);
        echo json_encode(['message' => 'ID de notificación no proporcionado.']);
        exit;
    }

    $sql = "UPDATE notificaciones SET leido = 1 WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $notificationId, $_SESSION['user_id']);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Notificación marcada como completada.']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error al marcar la notificación.']);
    }

    $stmt->close();
    $conn->close();
}
?>