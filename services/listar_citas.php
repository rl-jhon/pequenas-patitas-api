<?php
// Configuración de cabeceras para responder en formato JSON
header("Content-Type: application/json; charset=UTF-8");

// Importamos la conexión a la base de datos
require_once '../config/database.php';

// Validamos que el método de la petición sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido. Para listar las citas debe utilizar GET."
    ]);
    exit();
}

try {
    // Consultamos todas las citas registradas
    $query = "SELECT * FROM Cita ORDER BY fecha DESC, hora DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute();

    // Obtenemos todas las citas
    $citas = $stmt->fetchAll();

    // Respondemos con las citas encontradas
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Citas consultadas correctamente.",
        "data" => $citas
    ]);

} catch (PDOException $e) {
    // Manejo de errores del servidor
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error en el servidor al consultar las citas."
    ]);
}
?>