<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $data = json_decode(file_get_contents('php://input'), true);
    $vehicle_id = $data['vehicle_id'] ?? null;

    if (!$vehicle_id) {
        echo json_encode(['success' => false, 'error' => 'Falta el ID del vehículo']);
        exit;
    }

    try {
        $db = new Database();
        $conn = $db->connect();

        $conn->beginTransaction();

        $updateVehicleQuery = "UPDATE vehiculo SET estado = 'desocupado' WHERE id_vehiculo = :vehicle_id";
        $stmt = $conn->prepare($updateVehicleQuery);
        $stmt->execute([':vehicle_id' => $vehicle_id]);

        $deleteRentalQuery = "DELETE FROM alquiler WHERE id_vehiculo = :vehicle_id AND id_usuario = :user_id";
        $stmt = $conn->prepare($deleteRentalQuery);
        $stmt->execute([':vehicle_id' => $vehicle_id, ':user_id' => $user_id]);

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Alquiler cancelado exitosamente']);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'error' => 'Error al cancelar el alquiler: ' . $e->getMessage()]);
    } finally {
        $conn = null;
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
