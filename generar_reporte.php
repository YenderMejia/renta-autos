<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once 'db_config.php'; // Asegúrate de incluir tu archivo de configuración de DB

// Conexión a la base de datos
$db = new Database();
$conn = $db->connect();

// Consulta SQL actualizada
$query = "
    SELECT 
        v.placa AS placa_vehiculo,
        v.modelo AS modelo_vehiculo,
        v.marca AS marca_vehiculo,
        v.estado AS estado_vehiculo,
        c.tarifa AS tarifa_por_dia,
        COALESCE(a.estado, 'No alquilado') AS estado_alquiler,
        u.nombre AS nombre_usuario,
        f.fecha_emision AS fecha_factura,
        p.monto AS pago_realizado
    FROM vehiculo v
    LEFT JOIN categoria c ON v.id_categoria = c.id_categoria
    LEFT JOIN alquiler a ON v.id_vehiculo = a.id_vehiculo
    LEFT JOIN usuario u ON a.id_usuario = u.id_usuario
    LEFT JOIN pago p ON p.id_alquiler = a.id_alquiler
    LEFT JOIN factura f ON f.id_alquiler = a.id_alquiler
    ORDER BY f.fecha_emision DESC;
";

$stmt = $conn->prepare($query);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear un nuevo objeto TCPDF
$pdf = new TCPDF();

// Establecer la información del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Reporte de Vehículos');

// Agregar una página
$pdf->AddPage();

// Establecer el título del reporte
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Reporte de Vehículos', 0, 1, 'C');

// Agregar espacio
$pdf->Ln(10);

// Establecer la fuente para el contenido
$pdf->SetFont('helvetica', '', 12);

// Crear la tabla de datos
$html = '<table border="1" cellpadding="5">';
$html .= '<tr>
            <th>Placa</th>
            <th>Modelo</th>
            <th>Marca</th>
            <th>Estado Vehículo</th>
            <th>Tarifa</th>
            <th>Estado Alquiler</th>
            <th>Usuario Alquiler</th>
            <th>Fecha Factura</th>
            <th>Pago Realizado</th>
          </tr>';

foreach ($data as $row) {
    $html .= '<tr>';
    $html .= '<td>' . ($row['placa_vehiculo'] ?? '-') . '</td>';
    $html .= '<td>' . ($row['modelo_vehiculo'] ?? '-') . '</td>';
    $html .= '<td>' . ($row['marca_vehiculo'] ?? '-') . '</td>';
    $html .= '<td>' . ($row['estado_vehiculo'] ?? '-') . '</td>';
    $html .= '<td>' . ($row['tarifa_por_dia'] ?? '-') . '</td>';
    $html .= '<td>' . ($row['estado_alquiler'] ?? '-') . '</td>';
    $html .= '<td>' . ($row['nombre_usuario'] ?? '-') . '</td>';
    $html .= '<td>' . ($row['fecha_factura'] ?? '-') . '</td>';
    $html .= '<td>' . ($row['pago_realizado'] ?? '-') . '</td>';
    $html .= '</tr>';
}

$html .= '</table>';

// Escribir el HTML en el PDF
$pdf->writeHTML($html, true, false, false, false, '');

// Cerrar y generar el PDF
$pdf->Output('reporte_vehiculos.pdf', 'I');
?>