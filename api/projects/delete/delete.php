<?php
function delete(int $id)
{
    $db = new Database();
    $q = $db->connection->prepare("DELETE FROM projects_new WHERE id=?");
    $q->bindValue(1, $id);
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
}
