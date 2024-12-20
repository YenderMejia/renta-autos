<?php
require_once 'db_config.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT n.id_notificacion, n.tipo, n.mensaje, n.fecha_envio, n.estado, u.nombre AS usuario
                            FROM notificacion n
                            LEFT JOIN usuario u ON n.id_usuario = u.id_usuario");
    $stmt->execute();
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'notificaciones' => $notificaciones]);
} catch (PDOException $e) {
    error_log('Error al obtener notificaciones: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al obtener notificaciones.']);
} finally {
    $db->close();
}
?>
