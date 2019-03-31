<?php
require_once '../headers.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (is_numeric($_POST['id']) && is_numeric($_POST['status']) && isset($_POST['adm_comment'])) {
        require_once '../../database.php';
        $db = new Database();
        $q = $db->connection->prepare("SELECT * FROM projects_new WHERE `id` = :id");
        $q->execute([':id' => $_POST['id']]);
        $res = $q->fetchObject();
        $curator = $res->curator;
        if ($res->status == $_POST['status']) {
            http_response_code(200);
            echo json_encode(['message' => 'Such value is already set']);
        } else {
            $curatorQuery = $db->connection->prepare("SELECT active_projects FROM users WHERE id=:curator");
            $curatorQuery->bindParam(':curator', $curator);
            $curatorQuery->execute();
            $curatorQueryResult = ($curatorQuery->fetch())[0];
            if (count($curatorQueryResult) > 0) {
                $curatorQueryResult = explode(',', $curatorQueryResult);
                array_push($curatorQueryResult, $_POST['id']);
                $curatorQueryResult = implode(',', $curatorQueryResult);
            } else {
                $curatorQueryResult .= ',' . $_POST['id'];
            }
            $q = $db->connection->prepare("UPDATE projects_new SET `status` = :status, `adm_comment` =:adm_comment WHERE `projects_new`.`id` = :project; UPDATE users SET active_projects=:active WHERE id=:curator");
            $q->bindParam(':status', $_POST['status']);
            $q->bindParam(':adm_comment', $_POST['adm_comment']);
            $q->bindParam(':project', $_POST['id']);
            $q->bindParam(':active', $curatorQueryResult);
            $q->bindParam(':curator', $curator);
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
