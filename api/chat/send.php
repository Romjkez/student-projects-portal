<?php
function send(int $project_id, int $author_id, string $message)
{
    $db = new Database();
    $sendQuery = $db->connection->prepare("INSERT INTO chat(message_id,project_id,author_id,message,created_at) VALUES (NULL,?,?,?,NULL)");
    $sendQuery->bindValue(1, $project_id);
    $sendQuery->bindValue(2, $author_id);
    $sendQuery->bindValue(3, trim($message));
    // $sendQuery->bindValue(4, $created_at);
    $result = $sendQuery->execute();
    if ($result) {
        http_response_code(201);
        $lastInsertQuery = $db->connection->prepare("SELECT LAST_INSERT_ID()");
        $lastInsertQuery->execute();
        $lastPostId = $lastInsertQuery->fetch()[0];
        $lastPost = $db->connection->prepare("SELECT chat.message_id,chat.message,chat.created_at,users.id,users.name,users.surname,users.middle_name FROM `chat` INNER JOIN users ON users.id=chat.author_id WHERE chat.message_id=?");
        $lastPost->bindValue(1, $lastPostId);
        $lastPost->execute();
        $res = $lastPost->fetchObject();
        $author = [
            'id' => $res->id,
            'name' => $res->name,
            'surname' => $res->surname,
            'middle_name' => $res->middle_name
        ];
        $res->author = $author;

        unset($res->id);
        unset($res->name);
        unset($res->surname);
        unset($res->middle_name);
        return $res;
    } else {
        http_response_code(422);
        return ['message' => $sendQuery->errorInfo()[2]];
    }
}
