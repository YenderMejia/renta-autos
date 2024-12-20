<?php
session_start();
require_once 'db_config.php';

var_dump($_POST); // Verifica los datos recibidos


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['placa_actual'])) {
        echo json_encode(['success' => false, 'message' => 'La placa del vehículo a actualizar es obligatoria.']);
        exit;
    }

    $placa_actual = filter_var($_POST['placa_actual'], FILTER_SANITIZE_STRING); // Placa actual del vehículo

    // Conectar a la base de datos
    $db = new Database();
    $conn = $db->connect();

    try {
        // Obtener los datos actuales del vehículo (para manejar actualizaciones parciales)
        $stmt_select = $conn->prepare("SELECT * FROM vehiculo WHERE placa = :placa_actual");
        $stmt_select->bindParam(':placa_actual', $placa_actual);
        $stmt_select->execute();
        $vehiculo_actual = $stmt_select->fetch(PDO::FETCH_ASSOC);

        if (!$vehiculo_actual) {
            echo json_encode(['success' => false, 'message' => 'Vehículo no encontrado.']);
            exit;
        }

        // Preparar los nuevos valores, tomando los valores actuales si no se proporcionan
        $modelo = isset($_POST['modelo']) && !empty($_POST['modelo']) ? filter_var($_POST['modelo'], FILTER_SANITIZE_STRING) : $vehiculo_actual['modelo'];
        $marca = isset($_POST['marca']) && !empty($_POST['marca']) ? filter_var($_POST['marca'], FILTER_SANITIZE_STRING) : $vehiculo_actual['marca'];
        $anio = isset($_POST['anio']) && !empty($_POST['anio']) ? filter_var($_POST['anio'], FILTER_SANITIZE_NUMBER_INT) : $vehiculo_actual['anio'];
        $autonomia = isset($_POST['autonomia']) && !empty($_POST['autonomia']) ? filter_var($_POST['autonomia'], FILTER_SANITIZE_NUMBER_INT) : $vehiculo_actual['autonomia'];
        $placa = isset($_POST['placa']) && !empty($_POST['placa']) ? filter_var($_POST['placa'], FILTER_SANITIZE_STRING) : $vehiculo_actual['placa'];
        $estado = isset($_POST['estado']) && !empty($_POST['estado']) ? $_POST['estado'] : $vehiculo_actual['estado'];
        $nombre_categoria = isset($_POST['nombre']) && !empty($_POST['nombre']) ? $_POST['nombre'] : null;
        $tarifa = isset($_POST['tarifa']) && !empty($_POST['tarifa']) ? filter_var($_POST['tarifa'], FILTER_SANITIZE_NUMBER_INT) : null;

        // Manejar la imagen (si se proporciona una nueva)
        $imagen = $vehiculo_actual['imagen']; // Mantener la imagen actual si no se proporciona una nueva
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            $imagen_nueva = $_FILES['imagen']['name'];
            $targetDir = 'uploads/';
            $targetFile = $targetDir . basename($imagen_nueva);

            // Verificar tipo de archivo (solo imágenes)
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($imageFileType, $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Solo se permiten imágenes (JPG, JPEG, PNG, GIF).']);
                exit;
            }

            // Eliminar la imagen anterior si existe
            if ($imagen && file_exists($targetDir . $imagen)) {
                unlink($targetDir . $imagen);
            }

            // Subir la nueva imagen
            move_uploaded_file($_FILES['imagen']['tmp_name'], $targetFile);
            $imagen = $imagen_nueva;
        }

        // Manejar la categoría si se proporciona
        $categoria_id = $vehiculo_actual['id_categoria'];
        if ($nombre_categoria && $tarifa) {
            // Intentar actualizar la tarifa si la categoría ya existe
            $stmt_update_categoria = $conn->prepare("UPDATE categoria SET tarifa = :tarifa WHERE nombre = :nombre");
            $stmt_update_categoria->bindParam(':tarifa', $tarifa);
            $stmt_update_categoria->bindParam(':nombre', $nombre_categoria);
            $stmt_update_categoria->execute();
        
            // Luego, obtener el id_categoria de la categoría
            $stmt_categoria_get = $conn->prepare("SELECT id_categoria FROM categoria WHERE nombre = :nombre");
            $stmt_categoria_get->bindParam(':nombre', $nombre_categoria);
            $stmt_categoria_get->execute();
            $categoria_id = $stmt_categoria_get->fetchColumn();
        
            // Si la categoría no existía, crearla
            if (!$categoria_id) {
                $stmt_categoria = $conn->prepare("INSERT INTO categoria (nombre, tarifa) VALUES (:nombre, :tarifa) RETURNING id_categoria");
                $stmt_categoria->bindParam(':nombre', $nombre_categoria);
                $stmt_categoria->bindParam(':tarifa', $tarifa);
                $stmt_categoria->execute();
                $categoria_id = $conn->lastInsertId();
            }
        }
        

        // Actualizar el vehículo
        $stmt_update = $conn->prepare("UPDATE vehiculo 
                                       SET modelo = :modelo, 
                                           marca = :marca, 
                                           anio = :anio, 
                                           autonomia = :autonomia, 
                                           placa = :placa, 
                                           estado = :estado, 
                                           id_categoria = :id_categoria, 
                                           imagen = :imagen 
                                       WHERE placa = :placa_actual");
        $stmt_update->bindParam(':modelo', $modelo);
        $stmt_update->bindParam(':marca', $marca);
        $stmt_update->bindParam(':anio', $anio);
        $stmt_update->bindParam(':autonomia', $autonomia);
        $stmt_update->bindParam(':placa', $placa);
        $stmt_update->bindParam(':estado', $estado);
        $stmt_update->bindParam(':id_categoria', $categoria_id);
        $stmt_update->bindParam(':imagen', $imagen);
        $stmt_update->bindParam(':placa_actual', $placa_actual);

        if ($stmt_update->execute()) {
            echo json_encode(['success' => true, 'message' => 'Vehículo actualizado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el vehículo.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
?>