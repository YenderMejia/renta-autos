<?php
require_once 'db_config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['notificationType'], $data['message'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
    exit;
}

$notificationType = $data['notificationType'];
$message = htmlspecialchars($data['message']);
$specificUsername = $data['specificUsername'] ?? null;

try {
    $db = new Database();
    $conn = $db->connect();

    if ($notificationType === 'all') {
        // Obtener IDs de todos los usuarios con rol "cliente"
        $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE rol = 'cliente'");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$users) {
            echo json_encode(['success' => false, 'message' => 'No se encontraron usuarios con rol cliente.']);
            exit;
        }

        // Insertar notificaciones para todos los usuarios
        $stmt = $conn->prepare("INSERT INTO notificacion (id_usuario, tipo, mensaje, fecha_envio, estado) VALUES (:id_usuario, 'General', :mensaje, NOW(), 'Enviado')");
        foreach ($users as $user) {
            $stmt->execute([':id_usuario' => $user['id_usuario'], ':mensaje' => $message]);
        }
    } elseif ($notificationType === 'specific' && $specificUsername) {
        // Obtener ID del usuario específico
        $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE nombre = :nombre LIMIT 1");
        $stmt->bindParam(':nombre', $specificUsername, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
            exit;
        }

        // Insertar notificación para el usuario específico
        $stmt = $conn->prepare("INSERT INTO notificacion (id_usuario, tipo, mensaje, fecha_envio, estado) VALUES (:id_usuario, 'Personal', :mensaje, NOW(), 'Enviado')");
        $stmt->execute([':id_usuario' => $user['id_usuario'], ':mensaje' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Tipo de notificación no válido o faltan datos.']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Notificación enviada correctamente.']);
} catch (PDOException $e) {
    error_log('Error en la base de datos: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
} finally {
    $db->close();
}
