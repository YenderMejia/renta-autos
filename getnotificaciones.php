<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once 'db_config.php'; // Conexión a la base de datos

$db = new Database();
$conn = $db->connect();

// Obtener el nombre del usuario desde GET
$nombre_usuario = $_GET['nombre_usuario'] ?? null;

if (!$nombre_usuario) {
    echo json_encode(['success' => false, 'message' => 'Nombre de usuario no proporcionado.']);
    exit;
}

try {
    // Obtener el ID del usuario basándose en el nombre
    $query = "SELECT id_usuario FROM usuario WHERE nombre = :nombre";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':nombre', $nombre_usuario, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
        exit;
    }

    $id_usuario = $user['id_usuario'];

    // Obtener las notificaciones del usuario
    $query = "SELECT mensaje, fecha_envio FROM notificacion WHERE id_usuario = :id_usuario";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();

    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'notificaciones' => $notificaciones]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

?>
