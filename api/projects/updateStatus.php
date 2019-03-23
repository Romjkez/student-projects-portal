<?php
require_once '../headers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../database.php';
    $db = new Database();
    $q = $db->connection->prepare("UPDATE projects_new SET status=2 WHERE projects_new.finish_date<NOW();
INSERT INTO projects_archieve SELECT * FROM projects_new WHERE projects_new.finish_date<NOW()-INTERVAL 1 MONTH;
DELETE FROM projects_new WHERE projects.deadline<NOW()-INTERVAL 1 MONTH");
    $res = $q->execute();
    http_response_code(200);
    echo ($res == 1) ? json_encode(['message' => true]) : json_encode(['message' => false]);
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}
