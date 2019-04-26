<?php
function send(int $project_id, int $author_id, string $message, $created_at)
{
    $db = new Database();
    $sendQuery = $db->connection->prepare("INSERT INTO chat(id,project_id,author_id,message,created_at) VALUES (NULL,?,?,?,?)");
    $sendQuery->bindValue(1, $project_id);
    $sendQuery->bindValue(2, $author_id);
    $sendQuery->bindValue(3, trim($message));
    $sendQuery->bindValue(4, $created_at);
    $result = $sendQuery->execute();
    if ($result) {
        http_response_code(201);
        $lastInsertQuery = $db->connection->prepare("SELECT LAST_INSERT_ID()");
        $lastInsertQuery->execute();
        $lastPostId = $lastInsertQuery->fetch()[0];
        $lastPost = $db->connection->prepare("SELECT * FROM chat WHERE id=?");
        $lastPost->bindValue(1, $lastPostId);
        $lastPost->execute();
        return json_encode($lastPost->fetchObject());
    } else {
        http_response_code(200);
        return json_encode(['message' => $sendQuery->errorInfo()[2]]);
    }
}
