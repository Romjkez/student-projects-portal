<?php
function get(int $project_id)
{
    $db = new Database();
    $getQuery = $db->connection->prepare("SELECT * FROM chat WHERE project_id=? ORDER BY id DESC");
    $getQuery->bindValue(1, $project_id);
    $getQuery->execute();
    http_response_code(200);
    if ($getQuery->rowCount() > 0) {
        $result = $getQuery->fetchAll(PDO::FETCH_OBJ);
        return $result;
    } else {
        return ['message' => null];
    }
}
