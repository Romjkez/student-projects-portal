<?php

use Firebase\JWT\JWT;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Expose-Headers: X-Auth-Token');
header('Access-Control-Allow-Headers: X-Auth-Token, Content-Type');

require_once '../../../constants.php';
require_once('../../../vendor/autoload.php');
require_once '../../utils/updateToken.php';
$headers = getallheaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
} else if (isset($headers['X-Auth-Token'])) {
    try {
        $token = JWT::decode($headers['X-Auth-Token'], SECRET_KEY, [ALGORITHM]);
        if ($token->exp > time()) {
            if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                if (is_numeric($_REQUEST['id'])) {
                    require_once '../../../database.php';
                    $db = new Database();
                    $projectQuery = $db->connection->prepare("SELECT curator,status FROM projects_new WHERE id=?");
                    $projectQuery->bindValue(1, $_REQUEST['id']);
                    $projectQuery->execute();
                    if ($projectQuery->rowCount() > 0) {
                        $project = $projectQuery->fetchObject();
                        if ($token->data->usergroup == 3 || ((int)$project->curator == $token->data->id && ((int)$project->status == 0 || (int)$project->status == 3))) {
                            $data = updateToken($token);
                            header('X-Auth-Token: ' . JWT::encode($data, SECRET_KEY, ALGORITHM));
                            require_once 'delete.php';
                            echo json_encode(delete((int)$_REQUEST['id']));
                        } else {
                            http_response_code(403);
                            echo json_encode(['message' => 'Невозможно удалить проект, не выполнено хотя бы одно из условий: 1) вы администратор; 2) вы куратор не прошедшего модерацию проекта']);
                        }
                    } else {
                        http_response_code(404);
                        echo json_encode(['message' => 'Проект с указанным ID не найден']);
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['message' => WRONG_OR_MISSING_PARAMS_ERROR]);
                }
            } else {
                http_response_code(405);
                echo json_encode(['message' => WRONG_METHOD_ERROR]);
            }
        } else {
            http_response_code(401);
            echo json_encode(['message' => EXPIRED_SESSION_ERROR]);
        }
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['message' => EXPIRED_SESSION_OR_WRONG_TOKEN_ERROR]);
    }
} else {
    http_response_code(400);
    echo json_encode(['message' => WRONG_OR_MISSING_HEADERS_ERROR]);
}
