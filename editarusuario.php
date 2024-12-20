<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leer el cuerpo de la solicitud y decodificar JSON
    $inputData = json_decode(file_get_contents('php://input'), true);

    // Verificar que el campo 'nombre_usuario' exista
    $nombreUsuario = isset($inputData['nombre_usuario']) ? htmlspecialchars(trim($inputData['nombre_usuario']), ENT_QUOTES, 'UTF-8') : null;

    if (empty($nombreUsuario)) {
        echo json_encode(['success' => false, 'message' => 'El nombre de usuario es obligatorio.']);
        exit;
    }

    $campos = [];
    $params = [':nombre_usuario' => $nombreUsuario];

    // Verificar y añadir los campos enviados
    if (!empty($inputData['nombre'])) {
        $nuevoNombre = htmlspecialchars(trim($inputData['nombre']), ENT_QUOTES, 'UTF-8');
        
        // Verificar si el nombre ya existe
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE nombre = :nombre AND nombre != :nombre_actual");
        $stmt->bindParam(':nombre', $nuevoNombre);
        $stmt->bindParam(':nombre_actual', $nombreUsuario);
        $stmt->execute();

        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'El nombre ya está registrado.']);
            exit;
        }

        $campos[] = "nombre = :nombre";
        $params[':nombre'] = $nuevoNombre;
    }

    if (!empty($inputData['correo'])) {
        $nuevoCorreo = htmlspecialchars(trim($inputData['correo']), ENT_QUOTES, 'UTF-8');
        
        // Verificar si el correo ya existe
        $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE correo = :correo AND nombre != :nombre_actual");
        $stmt->bindParam(':correo', $nuevoCorreo);
        $stmt->bindParam(':nombre_actual', $nombreUsuario);
        $stmt->execute();

        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'El correo ya está registrado.']);
            exit;
        }

        $campos[] = "correo = :correo";
        $params[':correo'] = $nuevoCorreo;
    }

    if (!empty($inputData['rol'])) {
        $campos[] = "rol = :rol";
        $params[':rol'] = htmlspecialchars(trim($inputData['rol']), ENT_QUOTES, 'UTF-8');
    }
    if (!empty($inputData['estado'])) {
        $campos[] = "estado = :estado";
        $params[':estado'] = htmlspecialchars(trim($inputData['estado']), ENT_QUOTES, 'UTF-8');
    }

    if (empty($campos)) {
        echo json_encode(['success' => false, 'message' => 'No se enviaron datos para actualizar.']);
        exit;
    }

    $query = "UPDATE usuario SET " . implode(', ', $campos) . " WHERE nombre = :nombre_usuario";

    try {
        $stmt = $conn->prepare($query);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado o sin cambios.']);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario.']);
    } finally {
        $db->close();
    }
}
?>
