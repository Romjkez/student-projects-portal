<?php
require_once '../headers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (is_numeric($_POST['project_id']) && is_numeric($_POST['worker_id']) && is_numeric($_POST['team']) && isset($_POST['role'])) {
        require_once '../../database.php';
        $project_id = (int)$_POST['project_id'];
        $worker_id = (int)$_POST['worker_id'];
        $team = (int)$_POST['team'];
        $db = new Database();
        $isExists = $db->connection->prepare("SELECT worker_id,project_id FROM applications WHERE worker_id=:worker AND project_id=:project");
        $isExists->bindParam(':worker', $_POST['worker_id']);
        $isExists->bindParam(':project', $_POST['project_id']);
        $isExists->execute();
        if ($isExists->rowCount() > 0) {
            // todo запретить записываться когда уже есть активные проекты
            echo json_encode(['message' => 'Вы уже подали заявку на этот проект']);
        } else {
            $q = $db->connection->prepare("INSERT INTO `applications` (`id`, `worker_id`, `project_id`, `team`, `role`, `status`, `comment`) VALUES (NULL, :worker, :project, :team, :role, '0', :comment)");

            $checkProjectQuery = $db->connection->prepare('SELECT id FROM `projects_new` WHERE id=:project');

            $checkUserQuery = $db->connection->prepare('SELECT id FROM `users` WHERE id = :id');
            $checkUserQuery->bindParam(':id', $worker_id);
            $checkUserQuery->execute();
            $userExists = $checkUserQuery->rowCount();
            if ($userExists > 0) {
                $checkProjectQuery->bindParam(':project', $project_id);
                $checkProjectQuery->execute();
                $projectExists = $checkProjectQuery->rowCount();
                if ($projectExists > 0) {
                    $q->bindParam(':worker', $worker_id);
                    $q->bindParam(':project', $project_id);
                    $q->bindParam(':team', $team);
                    $q->bindParam(':role', $_POST['role']);
                    $q->bindParam(':comment', $_POST['comment']);
                    $result = $q->execute();
                    if ($result == true) {
                        http_response_code(201);
                        echo json_encode(['message' => "true"]);
                    } else {
                        http_response_code(200);
                        echo json_encode(['message' => "false"]);
                    }
                } else {
                    http_response_code(200);
                    echo json_encode(['message' => 'Specified project_id refers to nonexistent project']);
                }
            } else {
                http_response_code(200);
                echo json_encode(['message' => 'Specified worker_id refers to nonexistent user']);
            }
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Specify all the necessary parameters']);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}
