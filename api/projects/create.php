<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_SESSION['email'] == $_POST['email'] || $_POST['api_key'] == 'android') {
        require_once '../../database.php';
        $db = new Database();
        $q = $db->connection->prepare('');


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
        if ($key == 'curator') {
            $data[$key] = '1'; // id куратора из БД
        } else if ($key == 'description') {
            $data[$key] = trim($value);
        } else $data[$key] = htmlspecialchars(trim($value));
    }
    return $data;
}