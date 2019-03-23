<?php
require_once '../headers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (is_numeric($_POST['id']) && is_numeric($_POST['status'])) {
        $db = new Database();
        $check = $db->connection->prepare("SELECT status FROM applications WHERE id=:id");
        $check->bindParam(':id', $_POST['id']);
        $check->execute();
        $checkResult = $check->fetch();
        if ($checkResult[0] == $_POST['status']) {
            http_response_code(200);
            echo json_encode(['message' => 'This value is already set']);
        } else {
            $q = $db->connection->prepare("UPDATE applications SET status=:status WHERE id=:id");
            $q->bindParam(':id', $_POST['id']);
            $q->bindParam(':status', $_POST['status']);
            // todo дописать
        }
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}
