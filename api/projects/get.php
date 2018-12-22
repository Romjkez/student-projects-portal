<?php
// get project by id,status or curator
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id'])) {
        getProjectById();
    } else if (isset($_GET['status']) && !isset($_GET['curator'])) {
        getProjectsByStatus();
    } else if (isset($_GET['curator']) && !isset($_GET['status'])) {
        getProjectsByCurator();
    } else if (isset($_GET['curator']) && isset($_GET['status'])) {
        getProjectByCuratorAndStatus($_GET['curator'], $_GET['status']);
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

function getProjectsByCurator() // curator is id or email
{
    if (is_numeric($_GET['curator'])) {
        getProjectByCuratorId($_GET['curator']);
    } else {
        require_once '../../database.php';
        $db = new Database();
        $q = $db->connection->prepare("SELECT * FROM users WHERE email=?");
        $q->bindParam(1, $_GET['curator']);
        $q->execute();
        if ($q->rowCount() > 0) {
            $res = $q->fetchObject();
            $curatorId = $res->id;
            $db->disconnect();
            getProjectByCuratorId($curatorId);
        } else {
            http_response_code(200);
            echo json_encode(['message' => 'No projects found']);
        }
    }


}

function getProjectByCuratorId($curatorId)
{ // curator must be numeric(id)
    require_once '../../database.php';
    $db = new Database();
    $q = $db->connection->prepare("SELECT * FROM projects WHERE curator=?");
    $q->bindParam(1, $curatorId);
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

function getProjectByCuratorAndStatus($curator, $status)
{
    $status = (int)preg_replace('/[^0-9]/', '', $status); // =0 if GET[status] does not contain numbers
    require_once '../../database.php';
    $db = new Database();
    if (is_numeric($_GET['curator'])) {
        $q = $db->connection->prepare("SELECT * FROM projects WHERE curator=? AND status=?");
        $q->bindParam(1, $curator);
        $q->bindParam(2, $status);
        $q->execute();
        if ($q->rowCount() > 0) {
            $res = $q->fetchAll(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode($res);
        } else {
            http_response_code(200);
            echo json_encode(['message' => 'No projects found']);
        }
    } else {
        $q = $db->connection->prepare("SELECT * FROM users WHERE email=?");
        $q->bindParam(1, $_GET['curator']);
        $q->execute();
        if ($q->rowCount() > 0) {
            $res = $q->fetchObject();
            $curatorId = $res->id;
            $q = $db->connection->prepare("SELECT * FROM projects WHERE curator=? AND status=?");
            $q->bindParam(1, $curatorId);
            $q->bindParam(2, $status);
            $q->execute();
            if ($q->rowCount() > 0) {
                $res = $q->fetchAll(PDO::FETCH_ASSOC);
                http_response_code(200);
                echo json_encode($res);
            } else {
                http_response_code(200);
                echo json_encode(['message' => 'No projects found']);
            }
        } else {
            http_response_code(200);
            echo json_encode(['message' => 'No projects found']);
        }
    }

}