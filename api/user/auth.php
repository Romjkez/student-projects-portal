<?php
header('Access-Control-Allow-Origin: *'); // allow cross-origin queries
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Expose-Headers: X-Auth-Token');
header('Access-Control-Allow-Headers: X-Auth-Token, Content-Type');

require_once('../../vendor/autoload.php');

use Firebase\JWT\JWT;
const SECRET_KEY = 'a4074458293g';
const ALGORITHM = 'HS512';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $headers = getallheaders();
    if (iconv_strlen($_POST['email']) > 0 && iconv_strlen($_POST['pass']) > 0) {
        require_once '../../database.php';
        $db = new Database();
        $data = prepareData($_POST);
        $q = $db->connection->prepare("SELECT * FROM users WHERE email=?");
        $q->bindParam(1, $data['email']);
        $q->execute();
        $res = $q->fetch(PDO::FETCH_ASSOC);

        if ($q->rowCount() > 0 && password_verify($data['pass'], $res['password']) == true) {

            try {
                $tokenId = base64_encode(random_bytes(32));
            } catch (Exception $e) {
            }
            $issuedAt = time();
            $expire = $issuedAt + 14400;
            $serverName = 'http://' . $_SERVER['HTTP_HOST'];
            $data = [
                'iat' => $issuedAt, // время создания токена
                'upd' => $issuedAt, // последнее обновление токена
                'jti' => $tokenId, // ID токена/сессии
                'iss' => $serverName, // автор токена - сервер
                'exp' => $expire, // время до которого валиден токен(14400 секунд)
                'data' => [
                    'email' => $_POST['email'],
                    'usergroup' => $res['usergroup'],
                ]
            ];
            $jwt = JWT::encode($data, SECRET_KEY, ALGORITHM);
            echo json_encode($jwt);
        } else echo json_encode(['message' => 'false']);
    } else if (isset($headers['X-Auth-Token'])) {
        try {
            $token = JWT::decode($headers['X-Auth-Token'], SECRET_KEY, [ALGORITHM]);
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['message' => 'Authorization required']);
        }

        if ($token->exp > time()) {
            $updated = time();
            $data = [
                'iat' => $token->iat,
                'upd' => $updated,
                'jti' => $token->jti,
                'iss' => $token->iss,
                'exp' => $token->exp + ($token->upd - $token->iat),
                'data' => [
                    'email' => $token->data->email,
                    'usergroup' => $token->data->usergroup
                ]
            ];
            header('X-Auth-Token: ' . JWT::encode($data, SECRET_KEY, ALGORITHM));
            echo json_encode(['message' => 'ok']);
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'Authorization expired']);
        }
    } else {
        http_response_code(401);
        $headers = getallheaders();
        echo json_encode(['message' => 'Required parameters are wrong or missing']);
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('Access-Control-Allow-Headers: X-Auth-Token, Content-Type');
    header('Access-Control-Allow-Origin: *');
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}
function prepareData($array)
{
    $data = [];
    foreach ($array as $key => $value) {
        $data[$key] = htmlspecialchars($value);
    }
    return $data;
}



