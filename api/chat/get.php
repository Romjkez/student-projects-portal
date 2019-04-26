<?php
function get(int $project_id)
{
    $db = new Database();
    $getQuery = $db->connection->prepare("SELECT chat.message_id,chat.message,chat.created_at,users.id,users.name,users.surname,users.middle_name FROM `chat` INNER JOIN users ON users.id=chat.author_id WHERE chat.project_id=? ORDER BY chat.message_id DESC");
    $getQuery->bindValue(1, $project_id);
    $getQuery->execute();
    http_response_code(200);
    if ($getQuery->rowCount() > 0) {
        $result = [];
        for ($i = 0; $i < $getQuery->rowCount(); $i++) {
            $row = $getQuery->fetchObject();
            $author = [
                'id' => $row->id,
                'name' => $row->name,
                'surname' => $row->surname,
                'middle_name' => $row->middle_name
            ];
            $row->author = $author;

            unset($row->id);
            unset($row->name);
            unset($row->surname);
            unset($row->middle_name);
            array_push($result, $row);
        }

        return $result;
    } else {
        return ['message' => null];
    }
}
