<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();

    // Verificar que el usuario ha iniciado sesión
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Usuario no autenticado']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Si es una solicitud POST, filtrar vehículos con parámetros
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $filters = json_decode(file_get_contents('php://input'), true);

        $category = $filters['category'] ?? null;
        $plate = $filters['plate'] ?? null;
        $status = $filters['status'] ?? null;

        $query = "SELECT 
                    v.id_vehiculo, v.marca, v.modelo, v.placa, v.anio, v.autonomia, 
                    v.estado, v.imagen, c.nombre AS categoria, c.tarifa 
                  FROM vehiculo v
                  JOIN categoria c ON v.id_categoria = c.id_categoria
                  WHERE 1=1";

        $params = [];

        if ($category) {
            $query .= " AND c.id_categoria = :category";
            $params[':category'] = $category;
        }
        if ($plate) {
            $query .= " AND v.placa LIKE :plate";
            $params[':plate'] = '%' . $plate . '%';
        }
        if ($status !== null) {
            $query .= " AND v.estado = :status";
            $params[':status'] = $status;
        }

        $query .= " ORDER BY v.marca, v.modelo";

        $stmt = $conn->prepare($query);
        $stmt->execute($params);

        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($vehicles);

    // Si es una solicitud GET, obtener vehículos ocupados del usuario autenticado
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $query = "SELECT 
                    v.id_vehiculo, v.marca, v.modelo, v.placa, v.estado 
                  FROM vehiculo v 
                  JOIN alquiler a ON v.id_vehiculo = a.id_vehiculo 
                  WHERE a.id_usuario = :user_id AND v.estado = 'ocupado'";

        $stmt = $conn->prepare($query);
        $stmt->execute([':user_id' => $user_id]);

        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($vehicles) {
            echo json_encode($vehicles);
        } else {
            echo json_encode(['message' => 'No hay vehículos ocupados para este usuario.']);
        }

    } else {
        echo json_encode(['error' => 'Método no permitido']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Error en el servidor: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn = null; // Cerrar conexión
    }
}
