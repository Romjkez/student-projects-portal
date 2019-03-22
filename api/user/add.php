<?php
require_once '../headers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['api_key'] == 'android') {
        require_once '../../database.php';
        $db = new Database();
        $data = prepareData($_POST);

        $pass = password_hash($data['pass'], PASSWORD_DEFAULT);
        $q = $db->connection->prepare("INSERT INTO `users` (`id`, `name`, `surname`, `middle_name`, `email`, `password`, `phone`, `stdgroup`, `description`, `avatar`, `usergroup`, `active_projects`, `finished_projects`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL)");

        $q->bindParam(1, $data['name']);
        $q->bindParam(2, $data['surname']);
        $q->bindParam(3, $data['middlename']);
        $q->bindParam(4, $data['email']);
        $q->bindParam(5, $pass);
        $q->bindParam(6, $data['tel']);
        $q->bindParam(7, $data['std_group']);
        $q->bindParam(8, $data['description']);
        $q->bindParam(9, $data['avatar']);
        $q->bindParam(10, $data['usergroup']);
        $result = $q->execute();

        if ($result == true) {
            logReg();
            http_response_code(201);
            echo json_encode(['message' => "true"]);
        } else {
            http_response_code(200);
            echo json_encode(['message' => "false"]);
        }
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Authorization required"]);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}
function prepareData($array)
{
    $data = [];
    foreach ($array as $key => $value) {
        if ($key == 'usergroup' && $value != 1 && $value != 2) {
            $data[$key] = '1';
        } else $data[$key] = htmlspecialchars(trim($value));
    }
    return $data;
}


function logReg()
{
    date_default_timezone_set("Europe/Moscow");
    $newArr = [];
    foreach ($_POST as $key => $value) {
        if ($value != '') {
            $newArr[$key] = $value;
        } else $newArr[$key] = 'NULL';
    }
    $newlog = "\n" . date('Y-m-d[H:i:s]') . ' | NAME:' . $newArr['name'] . ' | SURNAME:' . $newArr['surname'] . ' | MIDDLENAME:' . $newArr['middlename'] . ' | EMAIL:' . $newArr['email'] . ' | USERGROUP:' . $newArr['usergroup'];
    file_put_contents('../../log/reg.txt', $newlog, FILE_APPEND);
}
