<?php
// Configuración de cabeceras para que el servicio web responda estrictamente en JSON
header("Content-Type: application/json; charset=UTF-8");

// Importamos el archivo de conexión segura a MySQL
require_once '../config/database.php';

// Validamos que el método de la petición HTTP sea exclusivamente POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => "error", 
        "message" => "Método no permitido. Para registrarse debe utilizar POST."
    ]);
    exit();
}

// Leemos el flujo de datos JSON que viene en el cuerpo (body) de la petición
$data = json_decode(file_get_contents("php://input"), true);

// 1. CAPTURA Y SANITIZACIÓN: Extraemos los parámetros mapeados a la tabla Usuario
$nombres   = isset($data['nombres'])   ? trim($data['nombres'])   : null;
$apellidos = isset($data['apellidos']) ? trim($data['apellidos']) : null;
$correo    = isset($data['correo'])    ? trim($data['correo'])    : null;
$password  = isset($data['contraseña']) ? trim($data['contraseña']) : null; // Atrapamos con 'ñ' desde el JSON recibido
$telefono  = isset($data['telefono'])  ? trim($data['telefono'])  : null;
$direccion = isset($data['direccion']) ? trim($data['direccion']) : null;

// 2. VALIDACIÓN: Verificar que ningún campo obligatorio se vaya vacío
if (!$nombres || !$apellidos || !$correo || !$password || !$telefono) {
    http_response_code(400);
    echo json_encode([
        "status" => "error", 
        "message" => "Error de validación. Faltan diligenciar campos obligatorios."
    ]);
    exit();
}

try {
    // 3. CONTROL DE DUPLICADOS: Consultamos si el correo ya existe en MySQL para evitar colisiones
    $checkStmt = $pdo->prepare("SELECT id_usuario FROM Usuario WHERE correo = :correo");
    $checkStmt->execute([':correo' => $correo]);
    
    if ($checkStmt->fetch()) {
        http_response_code(400);
        echo json_encode([
            "status" => "error", 
            "message" => "El correo electrónico ingresado ya se encuentra registrado."
        ]);
        exit();
    }

    // 4. SEGURIDAD: Encriptamos la contraseña con BCRYPT antes de guardarla (Estándar de Seguridad)
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // 5. INSERCIÓN: Guardamos el registro usando Backticks en la columna `contraseña` por compatibilidad
    $query = "INSERT INTO Usuario (nombres, apellidos, correo, `contraseña`, telefono, direccion) 
              VALUES (:nombres, :apellidos, :correo, :contrasena, :telefono, :direccion)";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':nombres'     => $nombres,
        ':apellidos'   => $apellidos,
        ':correo'      => $correo,
        ':contrasena'  => $passwordHash, // Hash protegido, no texto plano
        ':telefono'    => $telefono,
        ':direccion'   => $direccion
    ]);

    // Respuesta de éxito en la creación del recurso (Código HTTP 201)
    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Usuario registrado satisfactoriamente en Pequeñas Patitas."
    ]);

} catch (PDOException $e) {
    // Manejo seguro de errores en caso de fallo en el servidor
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "Error en el servidor al procesar el registro: " . $e->getMessage()
    ]);
}
?>
