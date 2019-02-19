<?php
require_once '../headers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email'])) {
        require_once '../../database.php';
        $email = htmlspecialchars($_POST['email']);
        $db = new Database();
        $q = $db->connection->prepare('SELECT id FROM `users` WHERE email=?');
        $q->bindParam(1, $email);
        $q->execute();
        $result = $q->rowCount();
        http_response_code(200);
        if ($result < 1) {
            echo json_encode(['message' => "false"]);
        } else {
            echo json_encode(['message' => "true"]);
        }
    } else {
        http_response_code(200);
        echo json_encode(['message' => 'No email to check']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}
