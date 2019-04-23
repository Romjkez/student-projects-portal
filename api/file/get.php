<?php
function get(int $project_id)
{
    require_once '../../database.php';
    $db = new Database();
    $q = $db->connection->prepare("SELECT id,title,link FROM files WHERE project_id=?");
    $q->bindValue(1, $project_id);
    $q->execute();
    if ($q->rowCount() > 0) {
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    } else {
        return null;
    }
}
