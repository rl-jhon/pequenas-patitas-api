<?php

// Permite que la API responda la información en formato JSON.
header("Content-Type: application/json; charset=UTF-8");

// Incluye la conexión con la base de datos.
require_once "../config/database.php";

// Verifica que la solicitud HTTP sea de tipo GET.
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);

    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido. Utilice GET."
    ]);

    exit;
}

try {

    // Consulta todos los servicios disponibles registrados en la base de datos.
    $sql = "SELECT 
                id_servicio,
                nombre_servicio,
                descripcion,
                precio
            FROM Servicio
            ORDER BY id_servicio ASC";

    // Prepara la consulta para evitar problemas de seguridad.
    $stmt = $pdo->prepare($sql);

    // Ejecuta la consulta.
    $stmt->execute();

    // Obtiene todos los servicios encontrados.
    $servicios = $stmt->fetchAll();

    // Devuelve los servicios encontrados en formato JSON.
    echo json_encode([
        "status" => "success",
        "message" => "Servicios consultados correctamente.",
        "data" => $servicios
    ]);

} catch (PDOException $e) {

    // Devuelve un error si ocurre un problema con la base de datos.
    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Error al consultar los servicios."
    ]);
}

?>