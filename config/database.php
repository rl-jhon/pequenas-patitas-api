<?php
// Configuración de los parámetros de conexión a MySQL
$host = "localhost";
$db_name = "veterinaria_db"; // Tu base de datos real
$username = "root";          // Usuario por defecto de XAMPP
$password = "";              // Contraseña por defecto de XAMPP (vacía)

try {
    // Creamos la conexión PDO especificando codificación UTF-8
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    
    // Activamos el manejo de errores y excepciones de SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $exception) {
    // Si la conexión falla, devolvemos un error en formato JSON
    header("Content-Type: application/json");
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error de conexión a la base de datos veterinaria: " . $exception->getMessage()
    ]);
    exit();
}
?>
