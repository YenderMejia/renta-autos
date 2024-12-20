<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Sesión no iniciada.']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $db = new Database();
    $conn = $db->connect();

    try {
        $conn->beginTransaction();

        // Construir dinámicamente la consulta de actualización
        $fields = [];
        $params = [':id_usuario' => $user_id];

        if (!empty($_POST['nombre'])) {
            $fields[] = "nombre = :nombre";
            $params[':nombre'] = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
        }

        if (!empty($_POST['correo'])) {
            $fields[] = "correo = :correo";
            $params[':correo'] = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
        }

        if (!empty($_POST['nueva_clave'])) {
            if (empty($_POST['clave_actual']) || empty($_POST['confirmar_clave'])) {
                throw new Exception("Para cambiar la clave, debes ingresar la clave actual y confirmarla.");
            }

            // Verificar la clave actual
            $stmt_clave = $conn->prepare("SELECT clave FROM usuario WHERE id_usuario = :id_usuario");
            $stmt_clave->bindParam(':id_usuario', $user_id);
            $stmt_clave->execute();
            $clave_hash = $stmt_clave->fetchColumn();

            if (!$clave_hash || !password_verify($_POST['clave_actual'], $clave_hash)) {
                throw new Exception("La clave actual es incorrecta.");
            }

            if ($_POST['nueva_clave'] !== $_POST['confirmar_clave']) {
                throw new Exception("Las claves nuevas no coinciden.");
            }

            $fields[] = "clave = :clave";
            $params[':clave'] = password_hash($_POST['nueva_clave'], PASSWORD_BCRYPT);
        }

        if (!empty($_POST['pregunta']) || !empty($_POST['respuesta'])) {
            if (empty($_POST['pregunta']) || empty($_POST['respuesta'])) {
                throw new Exception("Si ingresas una pregunta o una respuesta, ambas son obligatorias.");
            }

            $fields[] = "pregunta = :pregunta";
            $fields[] = "respuesta = :respuesta";
            $params[':pregunta'] = filter_var($_POST['pregunta'], FILTER_SANITIZE_STRING);
            $params[':respuesta'] = password_hash($_POST['respuesta'], PASSWORD_BCRYPT);
        }

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $foto = $_FILES['foto']['name'];
            $targetDir = 'uploads/';
            $targetFile = $targetDir . basename($foto);
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
                throw new Exception("Error al subir la foto de perfil.");
            }

            $fields[] = "foto = :foto";
            $params[':foto'] = $foto;
        }

        if (empty($fields)) {
            throw new Exception("No se proporcionó ningún dato para actualizar.");
        }

        // Crear la consulta dinámica
        $query = "UPDATE usuario SET " . implode(", ", $fields) . " WHERE id_usuario = :id_usuario";
        $stmt_update = $conn->prepare($query);
        $stmt_update->execute($params);

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente.']);
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}
?>
