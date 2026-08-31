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

    // Consulta los veterinarios registrados en la base de datos.
    $sql = "SELECT
                id_veterinario,
                nombres,
                apellidos,
                especialidad,
                correo,
                telefono
            FROM Veterinario
            ORDER BY id_veterinario ASC";

    // Prepara la consulta para mayor seguridad.
    $stmt = $pdo->prepare($sql);

    // Ejecuta la consulta.
    $stmt->execute();

    // Obtiene todos los veterinarios encontrados.
    $veterinarios = $stmt->fetchAll();

    // Devuelve la información en formato JSON.
    echo json_encode([
        "status" => "success",
        "message" => "Veterinarios consultados correctamente.",
        "data" => $veterinarios
    ]);

} catch (PDOException $e) {

    // Maneja cualquier error producido durante la consulta.
    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Error al consultar los veterinarios."
    ]);
}

?>