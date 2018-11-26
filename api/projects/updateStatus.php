<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['email']) || $_POST['api_key'] == 'android') {
        require_once '../../database.php';
        $db = new Database();
        $q = $db->connection->prepare("UPDATE projects SET status=2 WHERE projects.deadline<NOW();
INSERT INTO projects_archieve SELECT * FROM projects WHERE projects.deadline<NOW()-INTERVAL 1 MONTH;
DELETE FROM projects WHERE projects.deadline<NOW()-INTERVAL 1 MONTH");
        $res = $q->execute();
        http_response_code(200);
        echo ($res == 1) ? json_encode(['message' => 'true']) : json_encode(['message' => 'false']);
    } else {
        http_response_code(401);
        echo json_encode(['message' => 'Authorization required']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}