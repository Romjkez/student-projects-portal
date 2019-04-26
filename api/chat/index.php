<?php

use Firebase\JWT\JWT;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, DELETE, PUT');
header('Access-Control-Expose-Headers: X-Auth-Token');
header('Access-Control-Allow-Headers: X-Auth-Token, Content-Type');
header('Content-Type: application/json');

require_once('../../vendor/autoload.php');
require_once '../../constants.php';

$headers = getallheaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
} else if (isset($headers['X-Auth-Token'])) {
    try {
        $token = JWT::decode($headers['X-Auth-Token'], SECRET_KEY, [ALGORITHM]);
        if ($token->exp > time()) {
            require_once '../../database.php';
            $db = new Database();
            $userId = (int)$token->data->id;
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                if (is_numeric($_POST['project_id']) && iconv_strlen($_POST['message']) > 0) {
                    $projectQuery = $db->connection->prepare("SELECT curator,members FROM projects_new WHERE id=?");
                    $projectQuery->bindValue(1, $_POST['project_id']);
                    $projectQuery->execute();
                    if ($projectQuery->rowCount() > 0) {
                        $project = $projectQuery->fetchObject();
                        $project->members = parseMembers($project->members);
                        if (in_array($userId, $project->members) || $userId == $project->curator) {
                            $data = updateToken($token);
                            header('X-Auth-Token: ' . JWT::encode($data, SECRET_KEY, ALGORITHM));
                            require_once 'send.php';
                            echo json_encode(send($_POST['project_id'], $token->data->id, $_POST['message']));
                        } else {
                            http_response_code(403);
                            echo json_encode(['message' => 'You are not allowed to proceed this request']);
                        }
                    } else {
                        http_response_code(200);
                        echo json_encode(['message' => 'Проект с таким ID не найден']);
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['message' => 'Specify required POST parameters correctly']);
                }
            } else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                if (is_numeric($_GET['project_id'])) {
                    $projectQuery = $db->connection->prepare("SELECT curator,members FROM projects_new WHERE id=?");
                    $projectQuery->bindValue(1, $_GET['project_id']);
                    $projectQuery->execute();
                    if ($projectQuery->rowCount() > 0) {
                        $project = $projectQuery->fetchObject();
                        $project->members = parseMembers($project->members);

                        if (in_array($userId, $project->members) || $userId == $project->curator) {
                            $data = updateToken($token);
                            header('X-Auth-Token: ' . JWT::encode($data, SECRET_KEY, ALGORITHM));
                            require_once 'get.php';
                            echo json_encode(get($_GET['project_id']));
                        } else {
                            http_response_code(403);
                            echo json_encode(['message' => 'You are not allowed to proceed this request']);
                        }
                    } else {
                        http_response_code(200);
                        echo json_encode(['message' => 'Проект с таким ID не найден']);
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['message' => 'Specify required GET parameters correctly']);
                }
            } else if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
                if (is_numeric($_REQUEST['message_id'])) {
                    $messageQuery = $db->connection->prepare("SELECT author_id FROM chat WHERE message_id=?");
                    $messageQuery->bindValue(1, $_REQUEST['message_id']);
                    $messageQuery->execute();
                    if ($messageQuery->rowCount() > 0) {
                        $messageAuthor = $messageQuery->fetch()[0];

                        if ($messageAuthor == $userId) {
                            $data = updateToken($token);
                            header('X-Auth-Token: ' . JWT::encode($data, SECRET_KEY, ALGORITHM));
                            require_once 'delete.php';
                            echo json_encode(delete($_REQUEST['message_id']));
                        } else {
                            http_response_code(403);
                            echo json_encode(['message' => 'Удалить сообщение может только его автор']);
                        }
                    } else {
                        http_response_code(200);
                        echo json_encode(['message' => 'Проект с таким ID не найден']);
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['message' => 'Specify required parameters correctly']);
                }
            } else {
                http_response_code(405);
                echo json_encode(['message' => 'Method not supported']);
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
    http_response_code(400);
    echo json_encode(['message' => 'Required headers are wrong or missing']);
}
/**
 * @param $members
 * @return array
 */
function parseMembers($members)
{
    $result = [];
    $members = json_decode($members);
    for ($i = 0; $i < count($members); $i++) {
        foreach ($members[$i] as $value) {
            $value != 0 ? array_push($result, $value) : false;
        }
    }
    return $result;
}

/**
 * @param $token
 * @return array
 */
function updateToken($token)
{
    return [
        'iat' => $token->iat,
        'upd' => time(),
        'jti' => $token->jti,
        'iss' => $token->iss,
        'exp' => $token->exp + ($token->upd - $token->iat),
        'data' => [
            'email' => $token->data->email,
            'usergroup' => $token->data->usergroup,
            'id' => $token->data->id
        ]
    ];
}
