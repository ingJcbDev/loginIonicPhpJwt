<?php 
#<!-- Esta es una ruta protegida. Accedido solo por usuarios registrados -->

include_once 'config/ConexionPDO/Database.php';
include_once '../vendor/autoload.php';

use \Firebase\JWT\JWT;

include_once 'config/cors.php';

// obtener encabezados de solicitud
$authHeader = getallheaders();
if (isset($authHeader['Authorization']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $authHeader['Authorization'];
    $token = explode(" ", $token)[1];

    try {
        $key = "YOUR_SECRET_KEY";
        $decoded = JWT::decode($token, $key, array('HS256'));

        // Realice algunas acciones si el token se decodificó correctamente.

        // Pero para esta demostración, devuelva los datos decodificados.
        http_response_code(200);
        echo json_encode($decoded);
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(array('message' => 'Please authenticate'));
    }
} else {
    http_response_code(401);
    echo json_encode(array('message' => 'Please authenticate'));
}