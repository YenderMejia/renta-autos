<?php
session_start();
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'];
    $respuesta = $_POST['pregunta-seguridad'];
    $nueva_contraseña = $_POST['nueva-contraseña'];
    $confirmar_contraseña = $_POST['confirmar-contraseña'];

    // Verificar si las contraseñas coinciden
    if ($nueva_contraseña !== $confirmar_contraseña) {
        echo "Las contraseñas no coinciden.";
        exit;
    }

    // Conectar a la base de datos
    $db = new Database();
    $conn = $db->connect();

    try {
        // Verificar si el correo existe y obtener la respuesta de seguridad
        $stmt = $conn->prepare("SELECT id_usuario, respuesta FROM usuario WHERE correo = :correo");
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si la respuesta a la pregunta de seguridad es correcta
        if ($row && password_verify($respuesta, $row['respuesta'])) {
            // Si la respuesta es correcta, actualizar la contraseña
            $hashedPassword = password_hash($nueva_contraseña, PASSWORD_DEFAULT);

            // Actualizar la contraseña en la base de datos
            $updateStmt = $conn->prepare("UPDATE usuario SET clave = :clave WHERE correo = :correo");
            $updateStmt->bindParam(':clave', $hashedPassword, PDO::PARAM_STR);  // Se usa :clave aquí
            $updateStmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $updateStmt->execute();

            // Redirigir al login
            header("Location: login.html");
            exit;
        } else {
            echo "La respuesta a la pregunta de seguridad es incorrecta.";
        }
    } catch (PDOException $e) {
        echo "Error al actualizar la contraseña: " . $e->getMessage();
    }
}
?>
