<?php
require_once '../headers.php';
require_once '../../database.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_SESSION['email']) || $_GET['api_key'] == 'android') {
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            echo getUserByID($_GET['id']);
        } else if (isset($_GET['id']) && is_array($_GET['id'])) {
            echo getUsersById($_GET['id']);
        } else if (isset($_GET['email'])) {
            echo getUserByEmail($_GET['email']);
        } else if (isset($_GET['surname'])) {
            echo getUserBySurname($_GET['surname']);
        } else echo json_encode(["message" => "Unknown GET parameter"]);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Authorization required"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not supported"]);
}

function getUsersById($ids)
{
    $db = new Database();
    $resp = [];
    $q = $db->connection->prepare("SELECT id,name,surname,middle_name,email,phone,stdgroup,description,avatar,usergroup,active_projects,finished_projects FROM `users` WHERE id=:id");
    foreach ($ids as $key => $value) {
        $q->execute([':id' => (integer)$ids[$key]]);
        $rows = $q->rowCount();
        if ($rows === 0) $resp[] = null;
        else $resp[] = $q->fetchObject();
    }
    http_response_code(200);
    return json_encode($resp);
}

function getUserByID($id)
{
    $db = new Database();
    $stmt = $db->connection->prepare("SELECT id,name,surname,middle_name,email,phone,stdgroup,description,avatar,usergroup,active_projects,finished_projects FROM `users` WHERE id=:id");
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetchObject();
    $db->disconnect();
    if (empty($result)) {
        http_response_code(200);
        return json_encode(['message' => 'User not found']);
    } else {
        http_response_code(200);
        return json_encode($result);
    }

}

function getUserBySurname($surname)
{
    $db = new Database();
    $stmt = $db->connection->prepare("SELECT id,name,surname,middle_name,email,phone,stdgroup,description,avatar,usergroup,active_projects,finished_projects FROM `users` WHERE surname=?");
    $stmt->bindParam(1, $surname);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $db->disconnect();
    if (empty($result)) {
        http_response_code(200);
        return json_encode(['message' => 'User not found']);
    } else {
        http_response_code(200);
        return json_encode($result);
    }

}

function getUserByEmail($email)
{
    $db = new Database();
    $stmt = $db->connection->prepare("SELECT id,name,surname,middle_name,email,phone,stdgroup,description,avatar,usergroup,active_projects,finished_projects FROM `users` WHERE email=?");
    $stmt->bindParam(1, $email);
    $stmt->execute();
    $result = $stmt->fetchObject();
    $db->disconnect();
    if (empty($result)) {
        http_response_code(200);
        return json_encode(['message' => 'User not found']);
    } else {
        http_response_code(200);
        return json_encode($result);
    }
}
