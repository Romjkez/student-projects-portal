<?php
function delete()
{
    if (is_numeric($_REQUEST['id'])) {
        require_once '../../database.php';
        $db = new Database();
        $checkQuery = $db->connection->prepare("SELECT id FROM tags WHERE id=?");
        $checkQuery->bindValue(1, $_REQUEST['id']);
        $checkQuery->execute();
        if ($checkQuery->rowCount() > 0) {
            $q = $db->connection->prepare('DELETE FROM tags WHERE id=?');
            $q->bindValue(1, $_REQUEST['id']);
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
        return ['message' => 'Specify tag ID'];
    }
}
