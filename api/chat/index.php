<?php

use Firebase\JWT\JWT;

header('Access-Control-Allow-Origin: *');

require_once('../../vendor/autoload.php');
require_once '../headers.php';
require_once '../../constants.php';

$headers = getallheaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
} else if (isset($headers['X-Auth-Token'])) {
    try {
        $token = JWT::decode($headers['X-Auth-Token'], SECRET_KEY, [ALGORITHM]);
        if ($token->exp > time()) {
            require_once '../../database.php';
            $db = new Database();
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // TODO need test
                if (is_numeric($_POST['project_id']) && isset($_POST['message']) && isset($_POST['created_at'])) {
                    $projectQuery = $db->connection->prepare("SELECT curator,members FROM projects_new WHERE id=?");
                    $projectQuery->bindValue(1, $_POST['project_id']);
                    $projectQuery->execute();
                    $project = $projectQuery->fetchObject();
                    $project->members = parseMembers($project->members);
                    if (array_search($project->members, $token->data->id) || $token->data->id == $project->curator) {
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
                        require_once 'send.php';
                        echo send($_POST['project_id'], $token->data->id, $_POST['message'], $_POST['created_at']);
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
                    $project = $projectQuery->fetchObject();
                    $project->members = parseMembers($project->members);
                    if (array_search($project->members, $token->data->id) || $token->data->id == $project->curator) {
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
                        require_once 'get.php';
                        echo get($_GET['project_id']);
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['message' => 'Specify required GET parameters correctly']);
                }
            } else if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
                // todo FINISH DELETETION
                echo json_encode('DELETE method is not ready yet');
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
function parseMembers($members)
{
    $result = [];
    $members = json_decode($members);
    for ($i = 0; $i < count($members); $i++) {
        foreach ($members[$i] as $key => $value) {
            $value == 0 ? array_push($result, $value) : false;
        }
    }
    return $result;
}
