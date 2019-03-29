<?php
require_once '../headers.php';
require_once '../../database.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (is_numeric($_GET['worker']) && !isset($_GET['project'])) {
        echo getAppsByWorkerId();
    } else if (is_numeric($_GET['status']) && is_numeric($_GET['project'])) {
        echo getAppsByStatusAndProject();
    } else if (is_numeric($_GET['workerApplied']) && is_numeric($_GET['project'])) {
        echo isWorkerRequestedJoin();
    } else if (is_numeric($_GET['worker'])) {
        getUserApps();
    } else {
        http_response_code(200);
        echo json_encode(['message' => 'Specify GET parameters properly']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}

function getAppsByWorkerId()
{
    $db = new Database();
    $q = $db->connection->prepare("SELECT * FROM `applications` WHERE worker_id=:worker");
    $q->bindParam(':worker', $_GET['worker']);
    $q->execute();
    $rows = $q->rowCount();
    if ($rows > 0) {
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        http_response_code(200);
        return json_encode($result);
    } else {
        http_response_code(200);
        return json_encode(['message' => 'No applications found']);
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

function getUserApps()
{
    // todo подменять иды проектов их объектами
    $db = new Database();
    $q = $db->connection->prepare("SELECT * FROM applications WHERE worker=:worker ORDER BY id DESC");
    $q->bindParam(':worker', $_GET['worker']);
    $q->execute();
    $apps = $q->fetchAll();
    echo $apps;
}
