<?php
session_start();
require_once 'db_config.php';

try {
    // Conexión a la base de datos
    $db = new Database();
    $conn = $db->connect();

    $id_usuario = isset($_GET['id_usuario']) ? intval($_GET['id_usuario']) : 0;

    if ($id_usuario === 0) {
        echo json_encode(['success' => false, 'error' => 'ID de usuario inválido']);
        exit;
    }

    // Modificación de la consulta SQL para obtener el nombre del usuario junto con el mensaje
    $query = "SELECT c.*, u.nombre AS nombre_usuario 
              FROM chat c
              JOIN usuario u ON c.id_usuario = u.id_usuario
              WHERE c.id_usuario = :id_usuario
              ORDER BY c.fecha_mensaje ASC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'mensajes' => $mensajes]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error al obtener mensajes: ' . $e->getMessage()]);
}
?>
