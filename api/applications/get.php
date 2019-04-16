<?php
require_once '../headers.php';
require_once '../../database.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (is_numeric($_GET['worker']) && !isset($_GET['project']) && is_numeric($_GET['page']) && is_numeric($_GET['per_page'])) {
        echo getAppsByWorkerId();
    } else if (is_numeric($_GET['status']) && is_numeric($_GET['project'])) {
        echo getAppsByStatusAndProject();
    } else if (is_numeric($_GET['workerApplied']) && is_numeric($_GET['project'])) {
        echo isWorkerRequestedJoin();
    } else {
        http_response_code(200);
        echo json_encode(['message' => 'Specify GET parameters properly']);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}

function getAppsByWorkerId()
{
    $db = new Database();
    $page = (int)$_GET['page'];
    $per_page = (int)$_GET['per_page'];

    $q = $db->connection->prepare("SELECT * FROM applications WHERE worker_id=:worker ORDER BY id DESC LIMIT :per_page OFFSET :page");
    $q->bindParam(':worker', $_GET['worker']);
    $q->bindValue(':per_page', $per_page, PDO::PARAM_INT);
    $q->bindValue(':page', ($page - 1) * $per_page, PDO::PARAM_INT);
    $q->execute();
    $rows = $q->rowCount();
    $result = $q->fetchAll(PDO::FETCH_ASSOC);
    if ($rows > 0) {
        $pagesQuery = $db->connection->prepare("SELECT id FROM applications WHERE worker_id=:worker");
        $pagesQuery->bindParam(':worker', $_GET['worker']);
        $pagesQuery->execute();
        $pages = ceil($pagesQuery->rowCount() / $per_page);

        $project = $db->connection->prepare("SELECT * FROM projects_new WHERE id=:id");
        foreach ($result as &$item) {
            $project->bindParam(':id', $item['project_id']);
            $project->execute();
            $item['project_id'] = $project->fetchObject();
        }
        return json_encode([
            'pages' => $pages,
            'page' => $page,
            'per_page' => $per_page,
            'data' => $result,
        ]);
    } else {
        http_response_code(200);
        return json_encode([
            'pages' => 0,
            'page' => $page,
            'per_page' => $per_page,
            'data' => null
        ]);
    }
}

function getAppsByStatusAndProject()
{
    $db = new Database();
    $q = $db->connection->prepare("SELECT * FROM `applications` WHERE project_id=:project AND status=:status");
    $q->bindParam(':project', $_GET['project']);
    $q->bindParam(':status', $_GET['status']);
    $q->execute();
    $rows = $q->rowCount();
    if ($rows > 0) {
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        $userQuery = $db->connection->prepare("SELECT id,name,surname,middle_name,email,phone,stdgroup,description,avatar,usergroup,active_projects,finished_projects FROM users WHERE users.id=:id");
        foreach ($result as &$item) {
            $userQuery->bindParam(':id', $item['worker_id']);
            $userQuery->execute();
            $item['worker_id'] = $userQuery->fetchObject();
        }
        http_response_code(200);
        return json_encode($result);
    } else {
        http_response_code(200);
        return json_encode(['message' => 'No applications found']);
    }
}

function isWorkerRequestedJoin()
{
    // todo проверять статус заявки: если заявка отклонена, то пользователь не реквестед джоин
    $db = new Database();
    $q = $db->connection->prepare("SELECT id FROM `applications` WHERE project_id=:project AND worker_id=:worker");
    $q->bindParam(':project', $_GET['project']);
    $q->bindParam(':worker', $_GET['workerApplied']);
    $q->execute();
    $rows = $q->rowCount();
    if ($rows > 0) {
        http_response_code(200);
        return json_encode(['message' => 'true']);
    } else {
        http_response_code(200);
        return json_encode(['message' => 'false']);
    }
}
