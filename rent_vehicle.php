<?php
// Inicia la sesión para acceder a las variables de sesión
session_start();

require_once 'db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $vehicleId = $input['vehicleId'] ?? null;
    $paymentType = $input['paymentType'] ?? null;
    $startDate = $input['startDate'] ?? null;
    $endDate = $input['endDate'] ?? null;

    if ($vehicleId && $paymentType && $startDate && $endDate) {
        try {
            $db = new Database();
            $conn = $db->connect();

            // Verificar que el usuario está logueado y obtener el id_usuario
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['error' => 'No estás logueado']);
                exit;
            }

            $userId = $_SESSION['user_id'];

            // Calcular el monto basado en la tarifa diaria
            $query = "SELECT c.tarifa, v.marca, v.modelo, v.placa, v.anio 
                      FROM vehiculo v 
                      JOIN categoria c ON v.id_categoria = c.id_categoria
                      WHERE v.id_vehiculo = :vehicleId";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':vehicleId', $vehicleId, PDO::PARAM_INT);
            $stmt->execute();
            $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($vehicle) {
                $dailyRate = $vehicle['tarifa'];
                $days = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24);
                $totalAmount = $days * $dailyRate;

                // Insertar el alquiler
                $insertQuery = "INSERT INTO alquiler (id_usuario, id_vehiculo, fecha_inicio, fecha_fin, estado) 
                                VALUES (:userId, :vehicleId, :startDate, :endDate, 'confirmado')";
                $stmt = $conn->prepare($insertQuery);
                $stmt->execute([
                    ':userId' => $userId,
                    ':vehicleId' => $vehicleId,
                    ':startDate' => $startDate,
                    ':endDate' => $endDate,
                ]);

                // Actualizar el estado del vehículo a "ocupado"
                $updateVehicleQuery = "UPDATE vehiculo SET estado = 'ocupado' WHERE id_vehiculo = :vehicleId";
                $stmt = $conn->prepare($updateVehicleQuery);
                $stmt->bindParam(':vehicleId', $vehicleId, PDO::PARAM_INT);
                $stmt->execute();

                // Insertar el pago
                $insertPaymentQuery = "INSERT INTO pago (id_alquiler, tipo_pago, fecha_pago, monto)
                                       VALUES (currval('alquiler_id_alquiler_seq'), :paymentType, CURRENT_DATE, :totalAmount)";
                $stmt = $conn->prepare($insertPaymentQuery);
                $stmt->execute([
                    ':paymentType' => $paymentType,
                    ':totalAmount' => $totalAmount,
                ]);

                // Generar la factura con detalles del vehículo
                $vehicleDetails = "Marca: " . $vehicle['marca'] . " | Modelo: " . $vehicle['modelo'] . " | Placa: " . $vehicle['placa'] . " | Año: " . $vehicle['anio'] . " | Fecha de inicio: " . $startDate . " | Fecha de fin: " . $endDate;

                $insertInvoiceQuery = "INSERT INTO factura (id_alquiler, nombre_empresa, ubicacion_empresa, id_pago, detalles, fecha_emision)
                                       VALUES (currval('alquiler_id_alquiler_seq'), 'Rent-Car Electricos', 'Manta', currval('pago_id_pago_seq'), :vehicleDetails, CURRENT_DATE)";
                $stmt = $conn->prepare($insertInvoiceQuery);
                $stmt->execute([
                    ':vehicleDetails' => $vehicleDetails,
                ]);

                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Vehículo no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error al registrar el alquiler: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Datos incompletos']);
    }
} else {
    echo json_encode(['error' => 'Método no permitido']);
}
