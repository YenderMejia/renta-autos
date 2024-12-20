<?php
require_once 'db_config.php';

// Obtener el término de búsqueda desde la URL
$search = isset($_GET['q']) ? $_GET['q'] : '';

// Conectar a la base de datos
$db = new Database();
$conn = $db->connect();

try {
    // Consulta para buscar usuarios que coincidan con el término
    $stmt = $conn->prepare("SELECT id_usuario, nombre, foto FROM usuario WHERE LOWER(nombre) LIKE LOWER(:search) LIMIT 10");
    $searchTerm = '%' . $search . '%';
    $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver los resultados en formato JSON
    echo json_encode($result);
} catch (PDOException $e) {
    error_log("Error en la base de datos: " . $e->getMessage());
    echo json_encode([]);
} finally {
    $db->close();
}
?>
