<?php
session_start();
require_once 'db_config.php';

// Obtener el ID del usuario desde la sesión
$user_id = $_SESSION['user_id'];

// Conectar a la base de datos
$db = new Database();
$conn = $db->connect();

try {
    // Consulta para obtener el nombre, correo y foto del usuario
    $stmt = $conn->prepare("SELECT nombre, correo, foto FROM usuario WHERE id_usuario = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        // Devolver el nombre, correo y foto en formato JSON
        echo json_encode([
            'nombre' => htmlspecialchars($row['nombre']),
            'correo' => htmlspecialchars($row['correo']),
            'foto' => $row['foto'] ? 'uploads/' . $row['foto'] : null // Asegura que la ruta sea correcta
        ]);
    } else {
        echo json_encode([
            'nombre' => 'Usuario',
            'correo' => 'No disponible',
            'foto' => null
        ]);
    }
} catch (PDOException $e) {
    error_log("Error en la base de datos: " . $e->getMessage());
    echo json_encode([
        'nombre' => 'Usuario',
        'correo' => 'No disponible',
        'foto' => null
    ]);
} finally {
    $db->close();
}
