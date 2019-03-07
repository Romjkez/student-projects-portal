<?php
require_once '../headers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['project_id']) && isset($_POST['worker_id']) && isset($_POST['team']) && isset($_POST['role'])) {
        require_once '../../database.php';
        $project_id = (int)$_POST['number'];
        $worker_id = (int)$_POST['worker_id'];
        $team = (int)$_POST['team'];
        $db = new Database();
        $q = $db->connection->prepare("INSERT INTO `applications` (`id`, `worker_id`, `project_id`, `team`, `role`, `status`) VALUES (NULL, :worker, :project, :team, :role, '0')");
        $q->bindParam(':worker', $worker_id);
        $q->bindParam(':project', $project_id);
        $q->bindParam(':team', $team);
        $q->bindParam(':role', $_POST['role']);
        $result = $q->execute();
        if ($result == true) {
            http_response_code(201);
            echo json_encode(['message' => "true"]);
        } else {
            http_response_code(200);
            echo json_encode(['message' => "false"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Specify all the necessary parameters']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}
