<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['api_key'] == 'android' || $_SESSION['usergroup'] == 3) {
        if (is_numeric($_POST['projectId']) && is_numeric($_POST['status']) && isset($_POST['adm_comment'])) {
            require_once '../../database.php';
            $db = new Database();
            $q = $db->connection->prepare("SELECT * FROM `projects` WHERE `id` = :id");
            $q->execute([':id' => $_POST['projectId']]);
            $res = $q->fetchObject();
            if ($res->status == $_POST['status']) {
                http_response_code(200);
                echo json_encode(['message' => 'Such value is already set']);
            } else {
                $q = $db->connection->prepare("UPDATE `projects` SET `status` = ?, `adm_comment` = ? WHERE `projects`.`id` = ?;");
                $q->bindParam(1, $_POST['status']);
                $q->bindParam(2, $_POST['adm_comment']);
                $q->bindParam(3, $_POST['projectId']);
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
        http_response_code(401);
        echo json_encode(['message' => 'Authorization required']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}