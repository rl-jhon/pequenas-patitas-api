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
        "message" => "Método no permitido. Para iniciar sesión debe utilizar POST."
    ]);
    exit();
}

// Leemos el flujo de datos JSON que viene en el cuerpo (body) de la petición
$data = json_decode(file_get_contents("php://input"), true);

// 1. CAPTURA: Extraemos las credenciales enviadas por el usuario
$correo   = isset($data['correo'])    ? trim($data['correo'])    : null;
$password = isset($data['contraseña']) ? trim($data['contraseña']) : null;

// 2. VALIDACIÓN: Verificar que los campos requeridos no estén vacíos
if (!$correo || !$password) {
    http_response_code(400);
    echo json_encode([
        "status" => "error", 
        "message" => "Falta ingresar usuario o contraseña."
    ]);
    exit();
}

try {
    // 3. CONSULTA: Buscamos al usuario en MySQL mediante su correo único
    $stmt = $pdo->prepare("SELECT * FROM Usuario WHERE correo = :correo");
    $stmt->execute([':correo' => $correo]);
    $user = $stmt->fetch();

    // 4. AUTENTICACIÓN Y VERIFICACIÓN
    // Comparamos la contraseña en texto plano recibida contra el Hash encriptado guardado en la BD
    if ($user && password_verify($password, $user['contraseña'])) {
        
        // Mensaje de éxito requerido por el caso de estudio de la guía
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Autenticación satisfactoria."
        ]);
        
    } else {
        // Mensaje de error requerido por el caso de estudio de la guía (Corregido sin el http_code huérfano)
        http_response_code(401);
        echo json_encode([
            "status" => "error",
            "message" => "Error en la autenticación."
        ]);
    }

} catch (PDOException $e) {
    // Manejo de errores seguro del lado del servidor
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "Error en el servidor de autenticación: " . $e->getMessage()
    ]);
}
?>
