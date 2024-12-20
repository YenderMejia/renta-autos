<?php
session_start();
require_once 'db_config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$db = new Database();
$conn = $db->connect();

$stmt = $conn->prepare("SELECT nombre, correo, pregunta, foto FROM usuario WHERE id_usuario = :id_usuario");
$stmt->bindParam(':id_usuario', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo json_encode($user);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Usuario no encontrado.']);
}
?>
