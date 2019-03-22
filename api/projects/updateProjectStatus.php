<?php
require_once '../headers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (is_numeric($_POST['id']) && is_numeric($_POST['status']) && isset($_POST['adm_comment'])) {
        require_once '../../database.php';
        $db = new Database();
        $q = $db->connection->prepare("SELECT * FROM `projects_new` WHERE `id` = :id");
        $q->execute([':id' => $_POST['id']]);
        $res = $q->fetchObject();
        if ($res->status == $_POST['status']) {
            http_response_code(200);
            echo json_encode(['message' => 'Such value is already set']);
        } else {
            $q = $db->connection->prepare("UPDATE `projects_new` SET `status` = ?, `adm_comment` = ? WHERE `projects_new`.`id` = ?;");
            $q->bindParam(1, $_POST['status']);
            $q->bindParam(2, $_POST['adm_comment']);
            $q->bindParam(3, $_POST['id']);
            $result = $q->execute();
            if (!$result) {
                http_response_code(200);
                echo json_encode(['message' => 'false']);
            } else {
                http_response_code(200);
                echo json_encode(['message' => 'true']);
            }
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'One or several parameters were not set']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}
