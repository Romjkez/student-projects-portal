<?php

use Firebase\JWT\JWT;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Expose-Headers: X-Auth-Token');
header('Access-Control-Allow-Headers: X-Auth-Token, Content-Type');

require_once '../../../constants.php';
require_once '../../utils/updateToken.php';
require_once('../../../vendor/autoload.php');


$headers = getallheaders();
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
} else if (isset($headers['X-Auth-Token'])) {
    try {
        $token = JWT::decode($headers['X-Auth-Token'], SECRET_KEY, [ALGORITHM]);
        if ($token->exp > time()) {
            if ($token->data->usergroup == 2) {
                $data = updateToken($token);
                header('X-Auth-Token: ' . JWT::encode($data, SECRET_KEY, ALGORITHM));

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    require_once 'create.php';
                    create();
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

