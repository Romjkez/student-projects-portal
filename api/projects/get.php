<?php
// get project by id,status or curator
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id'])) {
        getProjectById();
    } else if (isset($_GET['status'])) {
        getProjectsByStatus();
    } else if (isset($_GET['curator'])) {
        getProjectsByCurator();
    } else {
        http_response_code(200);
        echo json_encode(['message' => 'GET parameter is not specified']);
    }

} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}
function getProjectById()
{
    $id = (int)preg_replace('/[^0-9]/', '', $_GET['id']); // =0 if GET[id] does not contain numbers
    require_once '../../database.php';
    $db = new Database();
    $q = $db->connection->prepare("SELECT * FROM projects WHERE id=?");
    $q->bindParam(1, $id);
    $q->execute();
    if ($q->rowCount() > 0) {
        $res = $q->fetchObject();
        http_response_code(200);
        echo json_encode($res);
    } else {
        http_response_code(200);
        echo json_encode(['message' => 'No projects found']);
    }
}

function getProjectsByStatus()
{
    $status = (int)preg_replace('/[^0-9]/', '', $_GET['status']); // =0 if GET[status] does not contain numbers
    require_once '../../database.php';
    $db = new Database();
    $q = $db->connection->prepare("SELECT * FROM projects WHERE status=?");
    $q->bindParam(1, $status);
    $q->execute();
    if ($q->rowCount() > 0) {
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        http_response_code(200);
        echo json_encode($res);
    } else {
        http_response_code(200);
        echo json_encode(['message' => 'No projects found']);
    }
}

function getProjectsByCurator()
{
    $curator = (int)preg_replace('/[^0-9]/', '', $_GET['curator']); // =0 if GET[status] does not contain numbers
    require_once '../../database.php';
    $db = new Database();
    $q = $db->connection->prepare("SELECT * FROM projects WHERE curator=?");
    $q->bindParam(1, $curator);
    $q->execute();
    if ($q->rowCount() > 0) {
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        http_response_code(200);
        echo json_encode($res);
    } else {
        http_response_code(200);
        echo json_encode(['message' => 'No projects found']);
    }
}