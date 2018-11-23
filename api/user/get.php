<?php
// получение информации о пользователе по id, email, surname
require_once '../../database.php';
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        echo getUserByID($_GET['id']);
    } else if (isset($_GET['email'])) {
        echo getUserByEmail($_GET['email']);
    } else if (isset($_GET['surname'])) {
        echo getUserBySurname($_GET['surname']);
    } else echo json_encode(["message" => "Unknown GET parameter"]);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not supported"]);
}

function getUserByID($id)
{
    $db = new Database();
    $stmt = $db->connection->prepare("SELECT name,surname,middle_name,email,phone,stdgroup,description,avatar,usergroup FROM `users` WHERE id=?");
    $stmt->bindParam(1, $id);
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

function getUserBySurname($surname)
{
    $db = new Database();
    $stmt = $db->connection->prepare("SELECT name,surname,middle_name,email,phone,stdgroup,description,avatar,usergroup FROM `users` WHERE surname=?");
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
    $stmt = $db->connection->prepare("SELECT name,surname,middle_name,email,phone,stdgroup,description,avatar,usergroup FROM `users` WHERE email=?");
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