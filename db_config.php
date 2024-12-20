<?php
class Database {
    // Configuración de conexión
    private $host = "dpg-ctibu0lds78s73ed9940-a.oregon-postgres.render.com";  // Host donde está PostgreSQL
    private $database = "renta_autos";  // Nombre de la base de datos
    private $user = "renta_autos_user";  // Usuario de PostgreSQL
    private $password = "xFzy6sqxnczB42oW6If85dgjoUpz23mt";  // Contraseña de PostgreSQL
    private $conn = null;  // Variable para la conexión

    // Método para conectar a la base de datos
    public function connect() {
        try {
            // Cadena de conexión
            $dsn = "pgsql:host=$this->host;dbname=$this->database"; 
            
            // Creación de la conexión
            $this->conn = new PDO($dsn, $this->user, $this->password);
            
            // Establecer el modo de error para PDO
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Si la conexión es exitosa, devuelve el objeto de conexión
            return $this->conn;
        } catch(PDOException $e) {
            // Si ocurre un error, captura la excepción y muestra el mensaje
            echo "Error de conexión: " . $e->getMessage();
            exit;
        }
    }

    // Método para cerrar la conexión
    public function close() {
        $this->conn = null;
    }
}

?>
