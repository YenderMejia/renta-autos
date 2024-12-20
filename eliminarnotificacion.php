<?php
require_once 'db_config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_notificacion'])) {
    echo json_encode(['success' => false, 'message' => 'ID de notificación no proporcionado.']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("DELETE FROM notificacion WHERE id_notificacion = :id_notificacion");
    $stmt->bindParam(':id_notificacion', $data['id_notificacion'], PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró la notificación para eliminar.']);
    }
} catch (PDOException $e) {
    error_log('Error al eliminar notificación: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al eliminar la notificación: ' . $e->getMessage()]);
} finally {
    $db->close();
}

?>
