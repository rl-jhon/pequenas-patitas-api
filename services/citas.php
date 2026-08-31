<?php

// Permite que la API responda y reciba información en formato JSON.
header("Content-Type: application/json; charset=UTF-8");

// Incluye la conexión con la base de datos.
require_once "../config/database.php";

// Verifica que la solicitud HTTP sea de tipo POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido. Para registrar una cita debe utilizar POST."
    ]);

    exit;
}

// Obtiene los datos enviados en formato JSON.
$data = json_decode(file_get_contents("php://input"), true);

// Valida que se hayan recibido los campos obligatorios.
if (
    empty($data["fecha"]) ||
    empty($data["hora"]) ||
    empty($data["Mascota_id_mascota"]) ||
    empty($data["Veterinario_id_veterinario"]) ||
    empty($data["Servicio_id_servicio"])
) {
    http_response_code(400);

    echo json_encode([
        "status" => "error",
        "message" => "Los campos fecha, hora, mascota, veterinario y servicio son obligatorios."
    ]);

    exit;
}

// Captura los datos recibidos.
$fecha = trim($data["fecha"]);
$hora = trim($data["hora"]);
$estado = isset($data["estado"]) ? trim($data["estado"]) : "Pendiente";
$motivo = isset($data["motivo"]) ? trim($data["motivo"]) : null;

$mascota_id = $data["Mascota_id_mascota"];
$veterinario_id = $data["Veterinario_id_veterinario"];
$servicio_id = $data["Servicio_id_servicio"];

try {

    // Verifica que la mascota exista.
    $stmt = $pdo->prepare(
        "SELECT id_mascota FROM Mascota WHERE id_mascota = :id"
    );

    $stmt->execute([
        ":id" => $mascota_id
    ]);

    if (!$stmt->fetch()) {
        http_response_code(404);

        echo json_encode([
            "status" => "error",
            "message" => "La mascota indicada no existe."
        ]);

        exit;
    }

    // Verifica que el veterinario exista.
    $stmt = $pdo->prepare(
        "SELECT id_veterinario FROM Veterinario WHERE id_veterinario = :id"
    );

    $stmt->execute([
        ":id" => $veterinario_id
    ]);

    if (!$stmt->fetch()) {
        http_response_code(404);

        echo json_encode([
            "status" => "error",
            "message" => "El veterinario indicado no existe."
        ]);

        exit;
    }

    // Verifica que el servicio exista.
    $stmt = $pdo->prepare(
        "SELECT id_servicio FROM Servicio WHERE id_servicio = :id"
    );

    $stmt->execute([
        ":id" => $servicio_id
    ]);

    if (!$stmt->fetch()) {
        http_response_code(404);

        echo json_encode([
            "status" => "error",
            "message" => "El servicio indicado no existe."
        ]);

        exit;
    }

    // Inserta la nueva cita en la base de datos.
    $sql = "INSERT INTO Cita
            (fecha, hora, estado, motivo,
             Mascota_id_mascota,
             Veterinario_id_veterinario,
             Servicio_id_servicio)
            VALUES
            (:fecha, :hora, :estado, :motivo,
             :mascota_id,
             :veterinario_id,
             :servicio_id)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":fecha" => $fecha,
        ":hora" => $hora,
        ":estado" => $estado,
        ":motivo" => $motivo,
        ":mascota_id" => $mascota_id,
        ":veterinario_id" => $veterinario_id,
        ":servicio_id" => $servicio_id
    ]);

    // Devuelve una respuesta exitosa.
    http_response_code(201);

    echo json_encode([
        "status" => "success",
        "message" => "Cita registrada satisfactoriamente en Pequeñas Patitas.",
        "id_cita" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {

    // Maneja errores producidos durante el proceso.
    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Error al registrar la cita."
    ]);
}

?>