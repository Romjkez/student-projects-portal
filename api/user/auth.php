<?php
require_once '../headers.php';
require_once '../../constants.php';

require_once '../../vendor/autoload.php';
require_once '../utils/getAllHeaders.php';
use Firebase\JWT\JWT;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $headers = getallheaders();
    if (iconv_strlen($_POST['email']) > 0 && iconv_strlen($_POST['pass']) > 0) {
        authorizeByEmailAndPass();
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
                    'usergroup' => $token->data->usergroup,
                    'id' => $token->data->id,
                ]
            ];
            header('X-Auth-Token: ' . JWT::encode($data, SECRET_KEY, ALGORITHM));
            echo json_encode(['message' => 'true']);
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'Authorization expired']);
        }
    } else {
        http_response_code(401);
        $headers = getallheaders();
        echo json_encode(['message' => 'Required headers are wrong or missing']);
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}
function authorizeByEmailAndPass()
{
    require_once '../../database.php';
    $db = new Database();
    $data = prepareData($_POST);
    $q = $db->connection->prepare("SELECT * FROM users WHERE email=?");
    $q->bindParam(1, $data['email']);
    $q->execute();
    $res = $q->fetch(PDO::FETCH_ASSOC);

    if ($q->rowCount() > 0 && password_verify($data['pass'], $res['password']) == true) {
        $issuedAt = time();
        $expire = $issuedAt + SESSION_DURATION; // 24 hours
        $serverName = 'http://' . $_SERVER['HTTP_HOST'];
        $data = [
            'iat' => $issuedAt, // время создания токена
            'upd' => $issuedAt, // последнее обновление токена
            'iss' => $serverName, // автор токена - сервер
            'exp' => $expire, // время до которого валиден токен(24 hours)
            'data' => [
                'email' => $_POST['email'],
                'usergroup' => $res['usergroup'],
                'id' => $res['id'],
            ]
        ];
        $jwt = JWT::encode($data, SECRET_KEY, ALGORITHM);
        echo json_encode($jwt);
    } else echo json_encode(['message' => 'false']);
}

function prepareData($array)
{
    $data = [];
    foreach ($array as $key => $value) {
        $data[$key] = htmlspecialchars($value);
    }
    return $data;
}



