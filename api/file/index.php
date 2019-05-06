<?php
require_once '../headers.php';
require_once '../../constants.php';
require_once('../../vendor/autoload.php');

use Firebase\JWT\JWT;

$headers = getallheaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (is_numeric($_GET['project_id'])) {
        require_once 'get.php';
        echo json_encode(get($_GET['project_id']));
    } else {
        http_response_code(400);
        json_encode(['message' => 'ID проекта не указан']);
    }
} else if (isset($headers['X-Auth-Token'])) {
    try {
        $token = JWT::decode($headers['X-Auth-Token'], SECRET_KEY, [ALGORITHM]);
        if ($token->exp > time()) {
            require_once '../../database.php';
            $db = new Database();
            $isCuratorQuery = $db->connection->prepare("SELECT curator FROM projects_new WHERE id=?");
            $isCuratorQuery->bindValue(1, $_REQUEST['project_id']);
            $isCuratorQuery->execute();
            if ($isCuratorQuery->rowCount() > 0) {
                $curatorId = $isCuratorQuery->fetch()[0];
                if ($token->data->id == $curatorId) {
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
                        require_once 'upload.php';
                        upload();
                    } else {
                        http_response_code(405);
                        echo json_encode(['message' => WRONG_METHOD_ERROR]);
                    }
                } else {
                    http_response_code(403);
                    echo json_encode(['message' => FORBIDDEN_ERROR]);
                }
            } else {
                http_response_code(400);
                json_encode(['message' => 'Не найден ни один проект с таким project_id или project_id не указан']);
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
