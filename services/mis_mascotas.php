<?php

// Permite recibir y responder información en formato JSON.
header("Content-Type: application/json; charset=UTF-8");

// Incluye la conexión con la base de datos.
require_once "../config/database.php";

// Verifica que la solicitud sea de tipo GET.
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);

    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido. Utilice GET."
    ]);

    exit;
}

// Obtiene el ID del usuario enviado mediante la URL.
$usuario_id = isset($_GET["Usuario_id_usuario"])
    ? $_GET["Usuario_id_usuario"]
    : null;

// Verifica que el ID del usuario haya sido enviado.
if (empty($usuario_id)) {
    http_response_code(400);

    echo json_encode([
        "status" => "error",
        "message" => "Debe indicar el ID del usuario."
    ]);

    exit;
}

try {

    // Consulta las mascotas que pertenecen al usuario indicado.
    $sql = "SELECT 
                id_mascota,
                nombre,
                especie,
                raza,
                fecha_nacimiento,
                sexo,
                peso
            FROM Mascota
            WHERE Usuario_id_usuario = :usuario_id";

    // Prepara la consulta para evitar inyección SQL.
    $stmt = $pdo->prepare($sql);

    // Ejecuta la consulta enviando el ID del usuario.
    $stmt->execute([
        ":usuario_id" => $usuario_id
    ]);

    // Obtiene todas las mascotas encontradas.
    $mascotas = $stmt->fetchAll();

    // Devuelve la información en formato JSON.
    echo json_encode([
        "status" => "success",
        "message" => "Mascotas consultadas correctamente.",
        "data" => $mascotas
    ]);

} catch (PDOException $e) {

    // Maneja cualquier error producido durante la consulta.
    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Error al consultar las mascotas."
    ]);
}

?>