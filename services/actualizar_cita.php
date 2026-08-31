<?php

// Permite que la API responda en formato JSON.
header("Content-Type: application/json; charset=UTF-8");

// Incluye la conexión con la base de datos.
require_once "../config/database.php";

// Verifica que la solicitud HTTP sea de tipo PUT.
if ($_SERVER["REQUEST_METHOD"] !== "PUT") {
    http_response_code(405);

    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido. Para actualizar una cita debe utilizar PUT."
    ]);

    exit;
}

// Obtiene los datos enviados en formato JSON.
$data = json_decode(file_get_contents("php://input"), true);

// Valida que se haya recibido el ID de la cita.
if (empty($data["id_cita"])) {
    http_response_code(400);

    echo json_encode([
        "status" => "error",
        "message" => "El id_cita es obligatorio."
    ]);

    exit;
}

// Captura el ID de la cita.
$id_cita = $data["id_cita"];

try {

    // Verifica que la cita exista.
    $stmt = $pdo->prepare(
        "SELECT id_cita FROM Cita WHERE id_cita = :id"
    );

    $stmt->execute([
        ":id" => $id_cita
    ]);

    if (!$stmt->fetch()) {
        http_response_code(404);

        echo json_encode([
            "status" => "error",
            "message" => "La cita indicada no existe."
        ]);

        exit;
    }

    // Valida los campos necesarios para actualizar la cita.
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

    // Captura los nuevos datos.
    $fecha = trim($data["fecha"]);
    $hora = trim($data["hora"]);
    $estado = isset($data["estado"]) ? trim($data["estado"]) : "Pendiente";
    $motivo = isset($data["motivo"]) ? trim($data["motivo"]) : null;

    $mascota_id = $data["Mascota_id_mascota"];
    $veterinario_id = $data["Veterinario_id_veterinario"];
    $servicio_id = $data["Servicio_id_servicio"];

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

    // Actualiza la información de la cita.
    $sql = "UPDATE Cita SET
                fecha = :fecha,
                hora = :hora,
                estado = :estado,
                motivo = :motivo,
                Mascota_id_mascota = :mascota_id,
                Veterinario_id_veterinario = :veterinario_id,
                Servicio_id_servicio = :servicio_id
            WHERE id_cita = :id_cita";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":fecha" => $fecha,
        ":hora" => $hora,
        ":estado" => $estado,
        ":motivo" => $motivo,
        ":mascota_id" => $mascota_id,
        ":veterinario_id" => $veterinario_id,
        ":servicio_id" => $servicio_id,
        ":id_cita" => $id_cita
    ]);

    // Devuelve una respuesta exitosa.
    http_response_code(200);

    echo json_encode([
        "status" => "success",
        "message" => "Cita actualizada satisfactoriamente en Pequeñas Patitas."
    ]);

} catch (PDOException $e) {

    // Maneja errores producidos durante el proceso.
    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Error al actualizar la cita."
    ]);
}

?>