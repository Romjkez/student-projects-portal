<?php

use Firebase\JWT\JWT;

require_once '../../headers.php';
require_once '../../../constants.php';
require_once('../../../vendor/autoload.php');


$headers = getallheaders();
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
} else if (isset($headers['X-Auth-Token'])) {
    try {
        $token = JWT::decode($headers['X-Auth-Token'], SECRET_KEY, [ALGORITHM]);
        if ($token->exp > time()) {
            if ($token->data->usergroup == 2) {
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
                    require_once 'create.php';
                    create();
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
            echo json_encode(['message' => 'Сессия устарела или токен аутенфикации неверный']);
        }
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['message' => 'Сессия устарела или токен аутенфикации неверный']);
    }
} else {
    http_response_code(401);
    echo json_encode(['message' => 'Required headers are wrong or missing']);
}
