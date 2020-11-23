<?php

include_once 'config/ConexionPDO/Database.php';
include_once 'config/cors.php';

$conn = new Database();
  
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    $fname = $data->firstname;
	$lname = $data->lastname;
	$uname = $data->username;
    $pass = $data->password;

    // Hash Password
    $hashed = password_hash($pass, PASSWORD_DEFAULT);

    // Puede hacer la validacion como un nombre de usuario unico, etc.
    $query = "
                INSERT INTO PUBLIC.users (
                        firstname
                        ,lastname
                        ,username
                        ,password
                        )
                VALUES (
                        '$fname'
                        ,'$lname'
                        ,'$uname'
                        ,'$hashed'
                        );
            ";

    $resp = $conn->queryInsert($query)->response;
    
    if ($resp) {
        http_response_code(201);
        echo json_encode(array('message' => 'User created'));
    } else {
        http_response_code(500);
        echo json_encode(array('message' => 'Internal Server error'));
    }
} else {
    http_response_code(404);
}

