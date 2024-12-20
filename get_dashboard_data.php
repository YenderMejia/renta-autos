<?php
session_start();
require_once 'db_config.php';

// Conectar a la base de datos
$db = new Database();
$conn = $db->connect();

try {
    // Consultar el total de usuarios registrados
    $stmt_users = $conn->prepare("SELECT COUNT(*) AS total_usuarios FROM usuario");
    $stmt_users->execute();
    $total_usuarios = $stmt_users->fetch(PDO::FETCH_ASSOC)['total_usuarios'];

    // Consultar el total de vehículos disponibles
    $stmt_vehicles_available = $conn->prepare("SELECT COUNT(*) AS total_disponibles FROM vehiculo WHERE estado = 'desocupado'");
    $stmt_vehicles_available->execute();
    $total_disponibles = $stmt_vehicles_available->fetch(PDO::FETCH_ASSOC)['total_disponibles'];

    // Consultar el total de vehículos en uso
    $stmt_vehicles_in_use = $conn->prepare("SELECT COUNT(*) AS total_en_uso FROM vehiculo WHERE estado = 'ocupado'");
    $stmt_vehicles_in_use->execute();
    $total_en_uso = $stmt_vehicles_in_use->fetch(PDO::FETCH_ASSOC)['total_en_uso'];

    // Retornar los datos en formato JSON
    echo json_encode([
        'success' => true,
        'data' => [
            'total_usuarios' => $total_usuarios,
            'total_disponibles' => $total_disponibles,
            'total_en_uso' => $total_en_uso
        ]
    ]);
} catch (Exception $e) {
    // Manejo de errores
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener los datos: ' . $e->getMessage()
    ]);
}
?>
