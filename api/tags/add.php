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
        $db->disconnect();
        $errors = $q->errorInfo();
        if ($errors[2] == null) {
            http_response_code(201);
            echo json_encode(['message' => 'true']);
        } else {
            http_response_code(422);
            echo json_encode(['message' => $errors[2]]);
        }
    } else {
        http_response_code(200);
        echo json_encode(['message' => 'Specify both category and value']);
    }
}
