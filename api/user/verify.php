<?php
require_once '../headers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['api_key'] == 'android') {
        require_once '../../database.php';
        $db = new Database();
        $data = prepareData($_POST);
        $q = $db->connection->prepare("SELECT password FROM `users` WHERE email=?");
        $q->bindParam(1, $data['email']);
        $q->execute();
        $res = $q->fetch();

        if ($q->rowCount() > 0 && password_verify($data['pass'], $res[0]) == true) {
            echo json_encode(['message' => 'true']);
        } else echo json_encode(['message' => 'false']);
        http_response_code(200);
    } else {
        http_response_code(401);
        echo json_encode(['message' => 'Authorization required']);
    }
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
