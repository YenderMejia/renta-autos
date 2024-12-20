<?php
require_once 'db_config.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();

    $query = "SELECT id_categoria, nombre FROM categoria ORDER BY nombre ASC";
    $stmt = $conn->query($query);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($categories);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener categorías: ' . $e->getMessage()]);
}
?>
