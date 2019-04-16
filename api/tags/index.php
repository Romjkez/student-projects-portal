<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS, PUT, GET, DELETE');
header('Access-Control-Expose-Headers: X-Auth-Token');
header('Access-Control-Allow-Headers: X-Auth-Token, Content-Type');
require_once '../../constants.php';
require_once('../../vendor/autoload.php');

use Firebase\JWT\JWT;

$headers = getallheaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once 'get.php';
    get();
} else if (isset($headers['X-Auth-Token'])) {
    try {
        $token = JWT::decode($headers['X-Auth-Token'], SECRET_KEY, [ALGORITHM]);
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['message' => 'Authorization required']);
    }
    if ($token->exp > time()) {
        if ($token->data->usergroup == 3) {
            $updated = time();
            $data = [
                'iat' => $token->iat,
                'upd' => $updated,
                'jti' => $token->jti,
                'iss' => $token->iss,
                'exp' => $token->exp + ($token->upd - $token->iat),
                'data' => [
                    'email' => $token->data->email,
                    'usergroup' => $token->data->usergroup,
                    'id' => $token->data->id
                ]
            ];
            header('X-Auth-Token: ' . JWT::encode($data, SECRET_KEY, ALGORITHM));

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                require_once 'add.php';
                add();
            } else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                require_once 'update.php';
                update();
            } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                require_once 'delete.php';
                delete();
            } else {
                http_response_code(405);
                echo json_encode(['message' => 'Method not supported']);
            }
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'You are not allowed to proceed this request']);
        }
    } else {
        http_response_code(401);
        echo json_encode(['message' => 'Authorization expired']);
    }
} else {
    http_response_code(401);
    $headers = getallheaders();
    echo json_encode(['message' => 'Required headers are wrong or missing']);
}
