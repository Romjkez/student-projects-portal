<?php

use Firebase\JWT\JWT;

ini_set('error_reporting', 1);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Expose-Headers: X-Auth-Token');
header('Access-Control-Allow-Headers: X-Auth-Token, Content-Type');

require_once '../../../constants.php';
require_once '../../utils/updateToken.php';
require_once '../../../vendor/autoload.php';
require_once '../../utils/getAllHeaders.php';

$headers = getallheaders();
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
} else if (isset($headers['X-Auth-Token'])) {
    try {
        $token = JWT::decode($headers['X-Auth-Token'], SECRET_KEY, [ALGORITHM]);
        if ($token->exp > time()) {
            if (isArgumentsValid()) {
                if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                    require_once '../../../database.php';
                    $db = new Database();
                    $projectQuery = $db->connection->prepare("SELECT curator FROM projects_new WHERE id=?");
                    $projectQuery->bindValue(1, $_REQUEST['id']);
                    $projectQuery->execute();

                    if ($token->data->id == $projectQuery->fetch()[0]) {
                        $db->disconnect();
                        $data = updateToken($token);
                        header('X-Auth-Token: ' . JWT::encode($data, SECRET_KEY, ALGORITHM));
                        require_once 'update.php';
                        update();
                    } else {
                        http_response_code(403);
                        echo json_encode(['message' => FORBIDDEN_ERROR]);
                    }
                } else {
                    http_response_code(405);
                    echo json_encode(['message' => WRONG_METHOD_ERROR]);
                }
            } else {
                http_response_code(400);
                echo json_encode(['message' => 'Укажите все необходимые параметры. Длина заголовка и описания должна превышать 2 символа']);
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

/**
 * @return bool
 */
function isArgumentsValid()
{
    if (iconv_strlen(trim($_REQUEST['title'])) > 2 && iconv_strlen(trim($_REQUEST['description'])) > 2 && is_numeric($_REQUEST['id']) && isset($_REQUEST['members'])
        && is_numeric($_REQUEST['curator']) && isset($_REQUEST['avatar']) && isset($_REQUEST['deadline'])
        && isset($_REQUEST['finish_date']) && isset($_REQUEST['tags'])) {
        return true;
    } else {
        return false;
    }
}
