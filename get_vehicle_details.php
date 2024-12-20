<?php
require_once 'db_config.php'; // Asegúrate de que esta conexión a la base de datos sea correcta

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Obtener el ID del vehículo desde la URL
    $vehicleId = $_GET['id'] ?? null;

    if ($vehicleId && is_numeric($vehicleId)) {
        try {
            $db = new Database();
            $conn = $db->connect();
    
            // Consulta SQL
            $query = "SELECT 
                        v.id_vehiculo, 
                        v.modelo, 
                        v.marca, 
                        v.anio, 
                        v.autonomia, 
                        v.estado, 
                        v.imagen, 
                        c.nombre AS categoria, 
                        c.tarifa 
                      FROM vehiculo v
                      JOIN categoria c ON v.id_categoria = c.id_categoria
                      WHERE v.id_vehiculo = :vehicleId";
    
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':vehicleId', $vehicleId, PDO::PARAM_INT);
            $stmt->execute();
    
            $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($vehicle) {
                echo json_encode($vehicle);
            } else {
                echo json_encode(['error' => 'Vehículo no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error al obtener los detalles: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'ID del vehículo no válido']);
    }
    
} else {
    echo json_encode(['error' => 'Método no permitido']);
}
