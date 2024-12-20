<?php
session_start();
require_once 'db_config.php';

try {
    // Conexión a la base de datos
    $db = new Database();
    $conn = $db->connect();

    // Obtener datos enviados por el cliente
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['destinatario'], $input['texto']) || empty(trim($input['texto']))) {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos o inválidos']);
        exit;
    }

    $id_usuario = intval($input['destinatario']); // Asegurar que sea un entero
    $mensaje = trim($input['texto']); // Limpiar el texto del mensaje

    if ($id_usuario <= 0 || empty($mensaje)) {
        echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
        exit;
    }

    // Insertar mensaje en la base de datos
    $query = "INSERT INTO chat (id_usuario, mensaje, fecha_mensaje, estado_mensaje) 
              VALUES (:id_usuario, :mensaje, NOW(), 'enviado')";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->bindParam(':mensaje', $mensaje, PDO::PARAM_STR);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error al enviar mensaje: ' . $e->getMessage()]);
}
?>  