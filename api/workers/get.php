<?php
// получение всех исполнителей
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if ((isset($_SESSION['email']) || $_GET['api_key'] == 'android')) {
        require_once '../../database.php';
        $db = new Database();
        $stmt = $db->connection->prepare("SELECT id,name,surname,middle_name,email,phone,stdgroup,description,avatar,usergroup FROM `users` WHERE usergroup=1");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) < 1) {
            http_response_code(200);
            echo json_encode(['message' => 'No users found']);
        } else {
            http_response_code(200);
            echo json_encode($result);
        }
        $db->disconnect();
    } else {
        http_response_code(401);
        echo json_encode(['message' => 'Authorization required']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}
