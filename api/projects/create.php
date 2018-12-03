<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['api_key'] == 'android') {
        require_once '../../database.php';
        $db = new Database();
        $q = $db->connection->prepare("INSERT INTO `projects` (`id`, `title`, `description`, `members`, `deadline`, `curator`, `tags`, `status`) VALUES (NULL,?,?,?,?,?,?,0)");
        $q->bindParam(1, $_POST['title']);
        $q->bindParam(2, $_POST['description']);
        $q->bindParam(3, $_POST['members']);
        $q->bindParam(4, $_POST['deadline']);
        $q->bindParam(5, $_POST['curator']);
        $q->bindParam(6, $_POST['tags']);
        $result = $q->execute();
        if ($result == true) {
            http_response_code(201);
            echo json_encode(['message' => "true"]);
        } else {
            http_response_code(200);
            echo json_encode(['message' => "false"]);
        }

    } else {
        http_response_code(401);
        echo json_encode(['message' => 'Authorization required']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}