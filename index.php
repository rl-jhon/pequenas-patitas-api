<?php
header("Content-Type: application/json");

// Intentamos incluir el archivo de configuración para probar la conexión
require_once 'config/database.php';

// Si no hay errores en el require, la conexión fue exitosa
echo json_encode([
    "status" => "success",
    "message" => "¡Conexión exitosa entre PHP y la base de datos veterinaria_db!"
]);
?>
