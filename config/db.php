<?php
// =============================================
// Configuración de conexión a la base de datos
// Archivo: config/db.php
// =============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Usuario por defecto en XAMPP
define('DB_PASS', '');           // Contraseña vacía por defecto en XAMPP
define('DB_NAME', 'parqueadero_db');

function conectar() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
?>
