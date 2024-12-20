<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar los datos enviados desde el formulario
    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
    $clave = $_POST['clave']; // Asegúrate de que el campo en el formulario HTML sea "clave"
    $captchaInput = $_POST['captcha-input'];
    $captchaHidden = $_POST['captcha-hidden'];

    // Verificar que los campos no estén vacíos
    if (empty($correo) || empty($clave) || empty($captchaInput)) {
        echo json_encode(['success' => false, 'error' => 'empty_fields']);
        exit;
    }

    if ($captchaInput !== $captchaHidden) {
        echo json_encode(['success' => false, 'error' => 'captcha']);
        exit;
    }

    // Conectar a la base de datos
    $db = new Database();
    $conn = $db->connect();

    try {
        // Verificar si el correo existe
        $stmt = $conn->prepare("SELECT id_usuario, clave, estado, rol FROM usuario WHERE correo = :correo");
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verificar si el usuario está activo
            if ($user['estado'] !== 'activo') {
                echo json_encode(['success' => false, 'error' => 'inactive_user']);
                exit;
            }

            if (password_verify($clave, $user['clave'])) {
                // Login exitoso
                $_SESSION['user_id'] = $user['id_usuario'];

                // Determinar redirección según el rol del usuario
                $redirectUrl = $user['rol'] === 'administrador' ? 'dashboard.html' : 'portal.html';

                echo json_encode([
                    'success' => true,
                    'token' => 'tokenGenerado123',
                    'redirect' => $redirectUrl
                ]);
            } else {
                // Error de credenciales
                echo json_encode(['success' => false, 'error' => 'invalid_credentials']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'user_not_found']);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'server_error']);
    } finally {
        $db->close();
    }
}
?>
