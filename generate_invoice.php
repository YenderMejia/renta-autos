<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once 'db_config.php';

session_start();

// Verifica que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    die('No estás logueado');
}

$userId = $_SESSION['user_id'];

try {
    $db = new Database();
    $conn = $db->connect();

    // Consulta para obtener las reservas del usuario
    $query = "SELECT 
                NOW() AS fecha_consulta,
                u.nombre AS nombre_usuario, 
                a.id_alquiler, 
                v.marca, 
                v.modelo, 
                v.placa, 
                a.fecha_inicio, 
                a.fecha_fin, 
                c.tarifa, 
                p.tipo_pago, 
                p.monto, 
                a.estado,
                p.fecha_pago AS fecha_emision
            FROM alquiler a
            JOIN vehiculo v ON a.id_vehiculo = v.id_vehiculo
            JOIN categoria c ON v.id_categoria = c.id_categoria
            JOIN pago p ON p.id_alquiler = a.id_alquiler
            JOIN usuario u ON a.id_usuario = u.id_usuario
            WHERE a.id_usuario = :userId AND a.estado = 'confirmado'";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verifica si hay reservas
    if (empty($reservations)) {
        die('No hay reservas confirmadas para este usuario.');
    }

    // Recuperar la fecha de consulta global
    $fechaConsulta = $reservations[0]['fecha_consulta'];

    // Generación del PDF
    $pdf = new TCPDF();
    $pdf->SetCreator('Rent-Car Electricos');
    $pdf->SetAuthor('Rent-Car Electricos');
    $pdf->SetTitle('Factura de Alquiler');
    $pdf->SetSubject('Detalles de Factura');
    $pdf->SetMargins(10, 10, 10);
    $pdf->AddPage();

    // Título de la factura
    $pdf->SetFont('Helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Factura de Alquiler - Rent-Car Electricos', 0, 1, 'C');
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->Cell(0, 10, 'Ubicación: Manta', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Fecha de consulta: ' . $fechaConsulta, 0, 1, 'C');
    $pdf->Ln(10);

    // Detalles de la factura
    foreach ($reservations as $reservation) {
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->Cell(0, 10, 'Cliente: ' . $reservation['nombre_usuario'], 0, 1);
        $pdf->Cell(0, 10, 'Fecha de inicio: ' . $reservation['fecha_inicio'], 0, 1);
        $pdf->Cell(0, 10, 'Fecha de fin: ' . $reservation['fecha_fin'], 0, 1);
        $pdf->Cell(0, 10, 'Vehículo: ' . $reservation['marca'] . ' ' . $reservation['modelo'], 0, 1);
        $pdf->Cell(0, 10, 'Placa: ' . $reservation['placa'], 0, 1);
        $pdf->Cell(0, 10, 'Tarifa: $' . $reservation['tarifa'], 0, 1);
        $pdf->Cell(0, 10, 'Tipo de pago: ' . $reservation['tipo_pago'], 0, 1);
        $pdf->Cell(0, 10, 'Monto: $' . $reservation['monto'], 0, 1);
        $pdf->Cell(0, 10, 'Estado del alquiler: ' . $reservation['estado'], 0, 1);
        $pdf->Ln(5); // Espacio entre reservas

        // Fecha de emisión (usada en cada reserva)
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Fecha de emisión: ' . $reservation['fecha_emision'], 0, 1, 'C');
    }

    // Output del PDF para mostrarlo en una nueva pestaña
    $pdf->Output('Factura_Reservas.pdf', 'I');

} catch (Exception $e) {
    echo 'Error al generar la factura: ' . $e->getMessage();
}
