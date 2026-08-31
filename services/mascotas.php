<?php

// Permite recibir y responder información en formato JSON.
header("Content-Type: application/json; charset=UTF-8");

// Incluye la conexión con la base de datos.
require_once "../config/database.php";

// Verifica que la solicitud sea de tipo POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido. Utilice POST."
    ]);

    exit();
}

// Obtiene los datos enviados desde Postman o desde el frontend.
$data = json_decode(file_get_contents("php://input"), true);

// Verifica que los datos recibidos tengan un formato JSON válido.
if ($data === null) {
    http_response_code(400);

    echo json_encode([
        "status" => "error",
        "message" => "Los datos enviados no tienen un formato JSON válido."
    ]);

    exit();
}

// Valida que los campos obligatorios hayan sido enviados.
if (
    empty($data["nombre"]) ||
    empty($data["especie"]) ||
    empty($data["Usuario_id_usuario"])
) {
    http_response_code(400);

    echo json_encode([
        "status" => "error",
        "message" => "Los campos nombre, especie y Usuario_id_usuario son obligatorios."
    ]);

    exit();
}

// Captura y limpia los datos recibidos.
$nombre = trim($data["nombre"]);
$especie = trim($data["especie"]);
$raza = isset($data["raza"]) ? trim($data["raza"]) : null;
$fecha_nacimiento = isset($data["fecha_nacimiento"])
    ? $data["fecha_nacimiento"]
    : null;
$sexo = isset($data["sexo"]) ? trim($data["sexo"]) : null;
$peso = isset($data["peso"]) ? $data["peso"] : null;
$usuario_id = $data["Usuario_id_usuario"];

try {

    // Verifica que el usuario exista antes de registrar la mascota.
    $usuarioStmt = $pdo->prepare(
        "SELECT id_usuario FROM Usuario WHERE id_usuario = :usuario_id"
    );

    $usuarioStmt->execute([
        ":usuario_id" => $usuario_id
    ]);

    if (!$usuarioStmt->fetch()) {
        http_response_code(404);

        echo json_encode([
            "status" => "error",
            "message" => "El usuario indicado no existe."
        ]);

        exit();
    }

    // Prepara la consulta para registrar la mascota.
    // Los parámetros evitan inyección SQL.
    $sql = "INSERT INTO Mascota
            (nombre, especie, raza, fecha_nacimiento, sexo, peso, Usuario_id_usuario)
            VALUES
            (:nombre, :especie, :raza, :fecha_nacimiento, :sexo, :peso, :usuario_id)";

    $stmt = $pdo->prepare($sql);

    // Ejecuta la consulta enviando los valores correspondientes.
    $stmt->execute([
        ":nombre" => $nombre,
        ":especie" => $especie,
        ":raza" => $raza,
        ":fecha_nacimiento" => $fecha_nacimiento,
        ":sexo" => $sexo,
        ":peso" => $peso,
        ":usuario_id" => $usuario_id
    ]);

    // Código HTTP 201 indica que el recurso fue creado correctamente.
    http_response_code(201);

    echo json_encode([
        "status" => "success",
        "message" => "Mascota registrada satisfactoriamente en Pequeñas Patitas.",
        "id_mascota" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {

    // Si ocurre un error en la base de datos, se informa al cliente.
    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Error en el servidor al registrar la mascota."
    ]);
}
?>