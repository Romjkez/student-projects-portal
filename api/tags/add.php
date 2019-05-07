<?php

function add()
{
    if (iconv_strlen($_POST['category']) > 1 && iconv_strlen($_POST['value']) > 1) {
        require_once '../../database.php';
        $db = new Database();
        $q = $db->connection->prepare("INSERT INTO tags (`id`,`category`,`value`) VALUES (NULL, ?, ?)");
        $q->bindValue(1, trim($_POST['category']));
        $q->bindValue(2, trim($_POST['value']));
        $q->execute();
        $errors = $q->errorInfo();
        if ($errors[2] == null) {
            $response = $db->connection->prepare("SELECT id,category,value FROM tags WHERE value=?");
            $response->bindValue(1, trim($_POST['value']));
            $response->execute();
            $responseItem = $response->fetchObject();
            $db->disconnect();
            http_response_code(201);
            return $responseItem;
        } else {
            http_response_code(422);
            return ['message' => $errors[2]];
        }
    } else {
        http_response_code(200);
        return ['message' => 'Specify both category and value'];
    }
}
