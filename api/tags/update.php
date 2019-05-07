<?php

function update()
{
    $data = [];
    $params = explode('&', file_get_contents('php://input'));
    foreach ($params as &$item) {
        $item = explode('=', $item);
        $data[$item[0]] = $item[1];
    }
    if (iconv_strlen($data['category']) > 1 && is_numeric($data['id']) && iconv_strlen($data['value']) > 1) {
        require_once '../../database.php';
        $db = new Database();
        $checkQuery = $db->connection->prepare("SELECT id FROM tags WHERE id=?");
        $checkQuery->bindValue(1, (int)$data['id']);
        $checkQuery->execute();
        if ($checkQuery->rowCount() > 0) {
            $q = $db->connection->prepare("UPDATE tags SET category=:cat, value=:val WHERE id=:id");
            $q->bindParam(':cat', $data['category']);
            $q->bindParam(':val', $data['value']);
            $q->bindParam(':id', $data['id']);
            $q->execute();
            $db->disconnect();
            $errors = $q->errorInfo();
            if ($errors[2] == null) {
                http_response_code(200);
                return ['message' => 'true'];
            } else {
                http_response_code(422);
                return ['message' => $errors[2]];
            }
        } else {
            http_response_code(404);
            return ['message' => 'Tag with such ID was not found'];
        }
    } else {
        http_response_code(400);
        return ['message' => 'Specify category, id and value to proceed'];
    }
}
