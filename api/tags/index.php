<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS, PUT, GET, DELETE');
header('Access-Control-Expose-Headers: X-Auth-Token');
header('Access-Control-Allow-Headers: X-Auth-Token, Content-Type');
require_once '../../constants.php';
require_once '../../vendor/autoload.php';
require_once '../utils/updateToken.php';
require_once '../utils/getAllHeaders.php';

use Firebase\JWT\JWT;

$headers = getallheaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once 'get.php';
    echo json_encode(get());
} else if (isset($headers['X-Auth-Token'])) {
    try {
        $token = JWT::decode($headers['X-Auth-Token'], SECRET_KEY, [ALGORITHM]);
        if ($token->exp > time()) {
            if ($token->data->usergroup == 3) {
                $data = updateToken($token);
                header('X-Auth-Token: ' . JWT::encode($data, SECRET_KEY, ALGORITHM));

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    require_once 'add.php';
                    echo json_encode(add());
                } else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                    require_once 'update.php';
                    echo json_encode(update());
                } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                    require_once 'delete.php';
                    echo json_encode(delete());
                } else {
                    http_response_code(405);
                    echo json_encode(['message' => WRONG_METHOD_ERROR]);
                }
            } else {
                http_response_code(403);
                echo json_encode(['message' => FORBIDDEN_ERROR]);
            }
        } else {
            http_response_code(401);
            echo json_encode(['message' => EXPIRED_SESSION_OR_WRONG_TOKEN_ERROR]);
        }
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['message' => EXPIRED_SESSION_OR_WRONG_TOKEN_ERROR]);
    }
} else {
    http_response_code(400);
    echo json_encode(['message' => WRONG_OR_MISSING_HEADERS_ERROR]);
}
