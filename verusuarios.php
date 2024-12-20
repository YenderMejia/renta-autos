<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $nombreUsuario = isset($_GET['nombre_usuario']) ? htmlspecialchars(trim($_GET['nombre_usuario']), ENT_QUOTES, 'UTF-8') : null;

    $db = new Database();
    $conn = $db->connect();

    try {
        if ($nombreUsuario) {
            // Consulta para buscar un usuario específico
            $query = "SELECT nombre, correo, rol, estado, fecha_creacion, fecha_actualizacion FROM usuario WHERE nombre LIKE :nombre";
            $stmt = $conn->prepare($query);
            $nombreUsuario = "%$nombreUsuario%";
            $stmt->bindParam(':nombre', $nombreUsuario, PDO::PARAM_STR);
        } else {
            // Consulta para obtener todos los usuarios
            $query = "SELECT nombre, correo, rol, estado, fecha_creacion, fecha_actualizacion FROM usuario";
            $stmt = $conn->prepare($query);
        }

        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $usuarios]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener los usuarios.']);
    } finally {
        $db->close();
    }
}
?>