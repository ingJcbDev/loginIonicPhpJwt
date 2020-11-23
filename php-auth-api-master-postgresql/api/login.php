<?php
include_once 'config/ConexionPDO/Database.php';
include_once '../vendor/autoload.php';

use \Firebase\JWT\JWT;

include_once 'config/cors.php';

$conn = new Database();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    $uname = $data->username;
    $pass = $data->password;

    $query = "
            SELECT *
            FROM users
            WHERE username = '$uname'
            ";

    $resp = $conn->querySelectFetchRowAssoc($query)->response;

    if (!empty($resp)) {
        $user = $resp;
        if (password_verify($pass, $user['password'])) {
            $key = "YOUR_SECRET_KEY"; // JWT KEY
            $payload = array(
                'user_id' => $user['id'],
                'username' => $user['username'],
                'firstname' => $user['firstname'],
                'lastname' => $user['lastname'],
            );

            $token = JWT::encode($payload, $key);
            http_response_code(200);
            echo json_encode(array('token' => $token));
        } else {
            http_response_code(400);
            echo json_encode(array('message' => 'Login Failed!'));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('message' => 'Login Failed!'));
    }
}
