<?php
function delete(int $post_id)
{
    $db = new Database();
    $deleteQuery = $db->connection->prepare("DELETE FROM chat WHERE message_id=?");
    $deleteQuery->bindValue(1, $post_id);
    $result = $deleteQuery->execute();
    if ($result) {
        http_response_code(200);
        return ['message' => 'true'];
    } else {
        http_response_code(422);
        return ['message' => $deleteQuery->errorInfo()[2]];
    }
}
