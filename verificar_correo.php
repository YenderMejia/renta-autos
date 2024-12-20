<?php
require_once 'db_config.php';

$data = json_decode(file_get_contents('php://input'), true);
$correo = $data['correo'];

$db = new Database();
$conn = $db->connect();

try {
    // Corregimos el nombre de la columna a 'pregunta'
    $stmt = $conn->prepare("SELECT id_usuario, pregunta FROM usuario WHERE correo = :correo");
    $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode([
            'existe' => true,
            'pregunta' => $row['pregunta']  // Usamos 'pregunta' aquí
        ]);
    } else {
        echo json_encode(['existe' => false]);
    }
} catch (PDOException $e) {
    echo json_encode(['existe' => false]);
}
