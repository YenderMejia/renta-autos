<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Asegurarse de que los datos están presentes
    if (empty($_POST['nombre']) || empty($_POST['correo']) || empty($_POST['clave']) || empty($_POST['confirm_clave']) || empty($_POST['rol']) || empty($_POST['estado'])) {
        echo json_encode(['success' => false, 'message' => 'El rol y el estado son obligatorios.']);
        exit;
    }

    $nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
    $clave1 = $_POST['clave'];
    $confirm_clave = $_POST['confirm_clave'];
    $rol = $_POST['rol'];
    $estado = $_POST['estado'];

    // Verificar que las contraseñas coincidan
    if ($clave1 !== $confirm_clave) {
        echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
        exit;
    }

    // Conectar a la base de datos
    $db = new Database();
    $conn = $db->connect();

    try {
        // Verificar si el correo ya existe
        $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE correo = :correo");
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'El correo electrónico ya está registrado.']);
            exit;
        }

        // Verificar si el nombre de usuario ya existe
        $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE nombre = :nombre");
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya esta registrado.']);
            exit;
        }

        // Crear el nuevo usuario
        $clave = password_hash($clave1, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO usuario (nombre, correo, clave, rol, estado) VALUES (:nombre, :correo, :clave, :rol, :estado)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':clave', $clave);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':estado', $estado);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Usuario agregado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario.']);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error de servidor.']);
    } finally {
        $db->close();
    }
}
?>
