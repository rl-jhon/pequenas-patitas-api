<?php

require_once "../config/cors.php";
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);

    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido."
    ]);

    exit;
}

if (!isset($_GET["Usuario_id_usuario"])) {
    http_response_code(400);

    echo json_encode([
        "status" => "error",
        "message" => "Debe proporcionar el ID del usuario."
    ]);

    exit;
}

$usuario_id = intval($_GET["Usuario_id_usuario"]);

try {

    $sql = "
        SELECT
            c.id_cita,
            c.fecha,
            c.hora,
            c.estado,
            c.motivo,
            m.id_mascota,
            m.nombre AS nombre_mascota,
            m.especie,
            v.id_veterinario,
            CONCAT(v.nombres, ' ', v.apellidos) AS nombre_veterinario,
            s.id_servicio,
            s.nombre_servicio
        FROM Cita c
        INNER JOIN Mascota m
            ON c.Mascota_id_mascota = m.id_mascota
        INNER JOIN Veterinario v
            ON c.Veterinario_id_veterinario = v.id_veterinario
        INNER JOIN Servicio s
            ON c.Servicio_id_servicio = s.id_servicio
        WHERE m.Usuario_id_usuario = :usuario_id
        ORDER BY c.fecha DESC, c.hora DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":usuario_id", $usuario_id, PDO::PARAM_INT);
    $stmt->execute();

    $citas = $stmt->fetchAll();

    http_response_code(200);

    echo json_encode([
        "status" => "success",
        "message" => "Citas consultadas correctamente.",
        "data" => $citas
    ]);

} catch (PDOException $exception) {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Error al consultar las citas: " . $exception->getMessage()
    ]);
}
?>