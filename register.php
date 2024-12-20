<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
    $clave1 = $_POST['clave'];
    $confirm_clave = $_POST['confirm_clave'];
    $captchaInput = $_POST['captcha-input'];
    $captchaHidden = $_POST['captcha-hidden'];

    // Verificar el captcha
    if ($captchaInput !== $captchaHidden) {
        header("Location: register.html?error=captcha");
        exit;
    }

    // Verificar que las contraseñas coincidan
    if ($clave1 !== $confirm_clave) {
        header("Location: register.html?error=password_mismatch");
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
            header("Location: register.html?error=email_exists");
            exit;
        }

        // Verificar si el nombre de usuario ya existe
        $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE nombre = :nombre");
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->fetch()) {
            header("Location: register.html?error=username_exists");
            exit;
        }

        // Crear el nuevo usuario
        $clave = password_hash($clave1, PASSWORD_DEFAULT);
        $rol = 'cliente';
        $estado = 'activo';

        $stmt = $conn->prepare("INSERT INTO usuario (nombre, correo, clave, rol, estado) VALUES (:nombre, :correo, :clave, :rol, :estado)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':clave', $clave);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':estado', $estado);
        
        if ($stmt->execute()) {
            header("Location: login.html?registration=success");
        } else {
            header("Location: register.html?error=registration_failed");
        }
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header("Location: register.html?error=server");
    } finally {
        $db->close();
    }
}
?>