<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si se ha recibido el campo de placa
    if (empty($_POST['placa'])) {
        echo json_encode(['success' => false, 'message' => 'La placa del vehículo es obligatoria.']);
        exit;
    }

    $placa = filter_var($_POST['placa'], FILTER_SANITIZE_STRING); // Sanitizar el valor de la placa

    // Conectar a la base de datos
    $db = new Database();
    $conn = $db->connect();

    try {
        // Verificar si el vehículo existe
        $stmt_select = $conn->prepare("SELECT * FROM vehiculo WHERE placa = :placa");
        $stmt_select->bindParam(':placa', $placa);
        $stmt_select->execute();
        $vehiculo = $stmt_select->fetch(PDO::FETCH_ASSOC);

        if (!$vehiculo) {
            echo json_encode(['success' => false, 'message' => 'Vehículo no encontrado.']);
            exit;
        }

        // Eliminar la imagen del vehículo si existe
        $imagen = $vehiculo['imagen'];
        if ($imagen) {
            $targetDir = 'uploads/';
            $imagePath = $targetDir . $imagen;

            if (file_exists($imagePath)) {
                unlink($imagePath); // Eliminar la imagen
            }
        }

        // Eliminar el vehículo de la base de datos
        $stmt_delete = $conn->prepare("DELETE FROM vehiculo WHERE placa = :placa");
        $stmt_delete->bindParam(':placa', $placa);

        if ($stmt_delete->execute()) {
            echo json_encode(['success' => true, 'message' => 'Vehículo eliminado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el vehículo.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
?>
