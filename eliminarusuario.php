<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputData = json_decode(file_get_contents('php://input'), true);

    $nombreUsuario = isset($inputData['nombre_usuario']) ? htmlspecialchars(trim($inputData['nombre_usuario']), ENT_QUOTES, 'UTF-8') : null;

    if (empty($nombreUsuario)) {
        echo json_encode(['success' => false, 'message' => 'El nombre de usuario es obligatorio.']);
        exit;
    }

    $db = new Database();
    $conn = $db->connect();

    try {
        // Preparar la consulta para eliminar el usuario
        $query = "DELETE FROM usuario WHERE nombre = :nombre_usuario";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':nombre_usuario', $nombreUsuario, PDO::PARAM_STR);

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Usuario eliminado correctamente.']);
        } else {
            //echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
        }
    } catch (PDOException $e) {
        //echo json_encode(['success' => false, 'message' => 'Error al eliminar el usuario.']);
    } finally {
        $db->close();
    }
}
?>
