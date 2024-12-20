<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    try {
        $db = new Database();
        $conn = $db->connect();

        // Consulta para obtener las reservas del usuario
        $query = "SELECT 
                    a.id_alquiler, 
                    v.marca, 
                    v.modelo, 
                    v.placa, 
                    a.fecha_inicio, 
                    a.fecha_fin, 
                    c.tarifa, 
                    p.tipo_pago, 
                    p.monto, 
                    a.estado 
                  FROM alquiler a
                  JOIN vehiculo v ON a.id_vehiculo = v.id_vehiculo
                  JOIN categoria c ON v.id_categoria = c.id_categoria
                  JOIN pago p ON p.id_alquiler = a.id_alquiler
                  WHERE a.id_usuario = :userId AND a.estado = 'confirmado'";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['reservations' => $reservations]);

    } catch (Exception $e) {
        echo json_encode(['error' => 'Error al obtener las reservas: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'No estás logueado']);
}
