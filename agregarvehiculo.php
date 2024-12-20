<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Asegurarse de que los datos están presentes
    if (empty($_POST['modelo']) || empty($_POST['marca']) || empty($_POST['anio']) || empty($_POST['autonomia']) || empty($_POST['placa']) || empty($_POST['estado']) || empty($_POST['nombre']) || empty($_POST['tarifa'])) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }

    $modelo = filter_var($_POST['modelo'], FILTER_SANITIZE_STRING);
    $marca = filter_var($_POST['marca'], FILTER_SANITIZE_STRING);
    $anio = filter_var($_POST['anio'], FILTER_SANITIZE_NUMBER_INT);
    $autonomia = filter_var($_POST['autonomia'], FILTER_SANITIZE_NUMBER_INT);
    $placa = filter_var($_POST['placa'], FILTER_SANITIZE_STRING);
    $estado = filter_var($_POST['estado'], FILTER_SANITIZE_STRING);
    $nombre_categoria = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
    $tarifa = filter_var($_POST['tarifa'], FILTER_SANITIZE_NUMBER_INT);

    // Subir imagen (si se proporciona)
    $imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $imagen = $_FILES['imagen']['name'];
        $targetDir = 'uploads/';
        $targetFile = $targetDir . basename($imagen);
        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $targetFile)) {
            echo json_encode(['success' => false, 'message' => 'Error al subir la imagen.']);
            exit;
        }
    }

    // Conectar a la base de datos
    $db = new Database();
    $conn = $db->connect();

    try {
        // Iniciar transacción
        $conn->beginTransaction();

        // Verificar si la categoría ya existe
        $stmt_categoria_get = $conn->prepare("SELECT id_categoria FROM categoria WHERE nombre = :nombre");
        $stmt_categoria_get->bindParam(':nombre', $nombre_categoria);
        $stmt_categoria_get->execute();
        $categoria_id = $stmt_categoria_get->fetchColumn();

        // Si la categoría no existe, crear una nueva
        if (!$categoria_id) {
            $stmt_categoria = $conn->prepare("INSERT INTO categoria (nombre, tarifa) VALUES (:nombre, :tarifa) RETURNING id_categoria");
            $stmt_categoria->bindParam(':nombre', $nombre_categoria);
            $stmt_categoria->bindParam(':tarifa', $tarifa);
            $stmt_categoria->execute();
            $categoria_id = $stmt_categoria->fetchColumn();
        }

        if (!$categoria_id) {
            throw new Exception("Error al obtener el ID de la categoría.");
        }

        // Insertar vehículo con el id_categoria
        $stmt_vehiculo = $conn->prepare("INSERT INTO vehiculo (modelo, marca, anio, autonomia, placa, estado, id_categoria, imagen) 
                                        VALUES (:modelo, :marca, :anio, :autonomia, :placa, :estado, :id_categoria, :imagen)");
        $stmt_vehiculo->bindParam(':modelo', $modelo);
        $stmt_vehiculo->bindParam(':marca', $marca);
        $stmt_vehiculo->bindParam(':anio', $anio);
        $stmt_vehiculo->bindParam(':autonomia', $autonomia);
        $stmt_vehiculo->bindParam(':placa', $placa);
        $stmt_vehiculo->bindParam(':estado', $estado);
        $stmt_vehiculo->bindParam(':id_categoria', $categoria_id);
        $stmt_vehiculo->bindParam(':imagen', $imagen);

        // Ejecutar la inserción del vehículo
        if ($stmt_vehiculo->execute()) {
            // Confirmar transacción
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Vehículo agregado correctamente.']);
        } else {
            throw new Exception("Error al agregar el vehículo.");
        }
    } catch (Exception $e) {
        // Si ocurre un error, hacer rollback y mostrar el error
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>
