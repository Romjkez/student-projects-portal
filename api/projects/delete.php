<?php
require_once '../headers.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (is_numeric($_REQUEST['id'])) {
        require_once '../../database.php';
        $db = new Database();
        $checkProjectQuery = $db->connection->prepare("SELECT status FROM projects_new WHERE id=:id");
        $checkProjectQuery->bindParam(':id', $_REQUEST['id']);
        $checkProjectQuery->execute();
        if ($checkProjectQuery->rowCount() > 0) {
            $projectStatus = $checkProjectQuery->fetch()[0];
            if ($projectStatus == 0 || $projectStatus == 3) {
                $deleteQuery = $db->connection->prepare("DELETE FROM projects_new WHERE id=:id");
                $deleteQuery->bindParam(':id', $_REQUEST['id']);
                $deleteQuery->execute();
                http_response_code(200);
                echo json_encode(['message' => 'true']);
            } else {
                http_response_code(200);
                echo json_encode(['message' => 'Нельзя удалять прошедшие модерацию проекты']);
            }
        } else {
            http_response_code(200);
            echo json_encode(['message' => 'Проект не найден']);
        }
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}
