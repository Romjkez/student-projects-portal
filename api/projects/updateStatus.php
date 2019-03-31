<?php
require_once '../headers.php';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../headers.php';
//todo сделать POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../../database.php';
    $db = new Database();
    // todo СДЕЛАТЬ ЗАПРОС ПЕРИОДИЧЕСКИМ и ВЫНЕСТИ ИЗ ГЛАВНОЙ СТРАНИЦЫ ФРОНТЕНДА
    // todo СДЕЛАТЬ ДОП. ТЕСТЫ
    $outdatedProjects = $db->connection->prepare("SELECT id,curator,members FROM projects_new WHERE finish_date<NOW()");
    $outdatedProjects->execute();
    $outdatedProjectsResult = $outdatedProjects->fetchAll(PDO::FETCH_ASSOC);
    if ($outdatedProjects->rowCount() > 0) {
        $curatorQuery = $db->connection->prepare("SELECT * FROM users WHERE id=:id");
        $curatorUpdateQuery = $db->connection->prepare("UPDATE users SET active_projects=:active, finished_projects=:finished WHERE id=:curator");
        foreach ($outdatedProjectsResult as &$item) {
            $curatorId = $item['curator'];
            $curatorQuery->bindParam(':id', $curatorId);
            $curatorQuery->execute();
            // update curator's projects
            $curator = $curatorQuery->fetchObject();
            $curator = updateUserProjects($curator, (int)$item['id']);
            $curatorUpdateQuery->bindParam(':active', $curator->active_projects);
            $curatorUpdateQuery->bindParam(':finished', $curator->finished_projects);
            $curatorUpdateQuery->bindParam(':curator', $curatorId);
            $curatorUpdateQuery->execute();

            // update users' projects
            $teams = json_decode($item['members']);
            foreach ($teams as $team) {
                foreach ($team as &$memberId) {
                    if ($memberId != 0) {
                        $curatorQuery->bindParam(':id', $memberId);
                        $curatorQuery->execute();
                        $member = $curatorQuery->fetchObject();
                        $member = updateUserProjects($member, (int)$item['id']);
                        $curatorUpdateQuery->bindParam(':active', $member->active_projects);
                        $curatorUpdateQuery->bindParam(':finished', $member->finished_projects);
                        $curatorUpdateQuery->bindParam(':curator', $memberId);
                        echo gettype($member->active_projects);
                        $curatorUpdateQuery->execute();
                    }
                }
            }
        }
    }

    $q = $db->connection->prepare("UPDATE projects_new SET status=2 WHERE projects_new.finish_date<NOW();
INSERT INTO projects_archieve SELECT * FROM projects_new WHERE projects_new.finish_date<NOW()-INTERVAL 1 MONTH;
DELETE FROM projects_new WHERE projects_new.finish_date<NOW()-INTERVAL 1 MONTH");
    $res = $q->execute();
    http_response_code(200);
    echo ($res == 1) ? json_encode(['message' => 'true']) : json_encode(['message' => 'false']);
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}

/**
 * @param $user
 * @param int $projectId
 * @return object
 */
function updateUserProjects($user, int $projectId)
{
    $active_projects = explode(',', $user->active_projects);
    if ($user->finished_projects != null) {
        $finished_projects = explode(',', $user->finished_projects);
    } else $finished_projects = [];

    $active_projects = array_filter($active_projects, function ($element) use ($projectId) {
        return ($element != $projectId);
    });

    if (!in_array($projectId, $finished_projects)) {
        array_push($finished_projects, $projectId);

    }

    $active_projects = implode(',', $active_projects);
    $finished_projects = implode(',', $finished_projects);

    $user->active_projects = $active_projects;
    $user->finished_projects = $finished_projects;
    return $user;
}

/*if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../database.php';
    $db = new Database();
    // todo при завершении проекта убирать проект из активных у пользователя и добавлять в законченные
    $q=$db->connection->prepare("SELECT id FROM applications");
    $q = $db->connection->prepare("UPDATE projects_new SET status=2 WHERE projects_new.finish_date<NOW();
INSERT INTO projects_archieve SELECT * FROM projects_new WHERE projects_new.finish_date<NOW()-INTERVAL 1 MONTH;
DELETE FROM projects_new WHERE projects.deadline<NOW()-INTERVAL 1 MONTH");
    $res = $q->execute();
    http_response_code(200);
    echo ($res == 1) ? json_encode(['message' => 'true']) : json_encode(['message' => 'false']);
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}*/
