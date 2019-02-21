<?php
require_once '../headers.php';
// get project by id,
// status or curator
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id'])) {
        getProjectById();
    } else if (isset($_GET['status']) && !isset($_GET['curator']) && ($_GET['page']) > 0 && $_GET['per_page'] > 0) {
        getProjectsByStatus();
    } else if (isset($_GET['curator']) && !isset($_GET['status']) && ($_GET['page']) > 0 && $_GET['per_page'] > 0) {
        getProjectsByCurator();
    } else if (isset($_GET['curator']) && isset($_GET['status']) && ($_GET['page']) > 0 && $_GET['per_page'] > 0) {
        getProjectByCuratorAndStatus($_GET['curator'], $_GET['status']);
    } else {
        http_response_code(200);
        echo json_encode(['message' => 'No valid GET parameters found']);
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
    $infoQuery = $db->connection->prepare('SELECT * FROM projects WHERE status=:status');
    $infoQuery->bindParam(':status', $status);
    $infoQuery->execute();
    $rows = $infoQuery->rowCount();
    $page = (int)$_GET['page'];
    $per_page = (int)$_GET['per_page'];
    if ($status === 30) {
        $q = $db->connection->prepare("SELECT * FROM projects WHERE status=0 OR status=3 LIMIT :per_page OFFSET :page");
        $q->bindValue(':per_page', $per_page, PDO::PARAM_INT);
        $q->bindValue(':page', ($page - 1) * $per_page, PDO::PARAM_INT);
        $q->execute();
    } else {
        $q = $db->connection->prepare("SELECT * FROM projects WHERE status=:status LIMIT :per_page OFFSET :page");
        $q->bindParam(':status', $status);
        $q->bindValue(':per_page', $per_page, PDO::PARAM_INT);
        $q->bindValue(':page', ($page - 1) * $per_page, PDO::PARAM_INT);
        $q->execute();
    }

    $pages = ceil($rows / $per_page);
    if ($q->rowCount() > 0) {
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        http_response_code(200);
        echo json_encode([
            'pages' => $pages + 1,
            'page' => $page,
            'per_page' => $per_page,
            'data' => $res
        ]);
    } else {
        http_response_code(200);
        echo json_encode([
            'pages' => $pages,
            'page' => $page,
            'per_page' => $per_page,
            'data' => null
        ]);
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
    $infoQuery = $db->connection->prepare('SELECT * FROM projects WHERE curator=:curator');
    $infoQuery->bindParam(':curator', $curatorId);
    $infoQuery->execute();
    $rows = $infoQuery->rowCount();
    $page = (int)$_GET['page'];
    $per_page = (int)$_GET['per_page'];

    $q = $db->connection->prepare("SELECT * FROM projects WHERE curator=:curator LIMIT :per_page OFFSET :page");
    $q->bindParam(':curator', $curatorId);
    $q->bindValue(':per_page', $per_page, PDO::PARAM_INT);
    $q->bindValue(':page', ($page - 1) * $per_page, PDO::PARAM_INT);
    $q->execute();
    $pages = ceil($rows / $per_page);

    if ($q->rowCount() > 0) {
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        http_response_code(200);
        echo json_encode([
            'pages' => $pages,
            'page' => $page,
            'per_page' => $per_page,
            'data' => $res
        ]);
    } else {
        http_response_code(200);
        echo json_encode([
            'pages' => $pages + 1,
            'page' => $page,
            'per_page' => $per_page,
            'data' => null
        ]);
    }
}

function getProjectByCuratorAndStatus($curator, $status)
{
    $status = (int)preg_replace('/[^0-9]/', '', $status); // =0 if GET[status] does not contain numbers
    require_once '../../database.php';
    $db = new Database();
    if (is_numeric($curator)) {
        $infoQuery = $db->connection->prepare("SELECT * FROM projects WHERE curator=:curator AND status=:status");
        $infoQuery->bindParam(':curator', $curator);
        $infoQuery->bindParam(':status', $status);
        $infoQuery->execute();
        $rows = $infoQuery->rowCount();
        $page = (int)$_GET['page'];
        $per_page = (int)$_GET['per_page'];
        if ($status === 30) {
            $q = $db->connection->prepare("SELECT * FROM projects WHERE curator=:curator AND status=0 OR status=3 LIMIT :per_page OFFSET :page");
            $q->bindParam(':curator', $curator);
            $q->bindValue(':per_page', $per_page, PDO::PARAM_INT);
            $q->bindValue(':page', ($page - 1) * $per_page, PDO::PARAM_INT);
            $q->execute();
        } else {
            $q = $db->connection->prepare("SELECT * FROM projects WHERE curator=:curator AND status=0 OR status=3 LIMIT :per_page OFFSET :page");
            $q->bindParam(':curator', $curator);
            $q->bindValue(':per_page', $per_page, PDO::PARAM_INT);
            $q->bindValue(':page', ($page - 1) * $per_page, PDO::PARAM_INT);
            $q->execute();
        }
        $pages = ceil($rows / $per_page);
        if ($q->rowCount() > 0) {
            $res = $q->fetchAll(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode([
                'pages' => $pages + 1,
                'page' => $page,
                'per_page' => $per_page,
                'data' => $res
            ]);
        } else {
            http_response_code(200);
            echo json_encode([
                'pages' => $pages,
                'page' => $page,
                'per_page' => $per_page,
                'data' => null
            ]);
        }
    } else {
        $q = $db->connection->prepare("SELECT * FROM users WHERE email=?");
        $q->bindParam(1, $_GET['curator']);
        $q->execute();
        if ($q->rowCount() > 0) {
            $res = $q->fetchObject();
            $curatorId = $res->id;
            $infoQuery = $db->connection->prepare("SELECT * FROM projects WHERE curator=:curatorId AND status=:status");
            $infoQuery->bindParam(':curatorId', $curatorId);
            $infoQuery->bindParam(':status', $status);
            $infoQuery->execute();
            $rows = $infoQuery->rowCount();
            $page = (int)$_GET['page'];
            $per_page = (int)$_GET['per_page'];

            $q = $db->connection->prepare("SELECT * FROM projects WHERE curator=:curatorId AND status=:status LIMIT :per_page OFFSET :page");
            $q->bindParam(':curatorId', $curatorId);
            $q->bindParam(':status', $status);
            $q->bindValue(':per_page', $per_page, PDO::PARAM_INT);
            $q->bindValue(':page', ($page - 1) * $per_page, PDO::PARAM_INT);
            $q->execute();
            $pages = ceil($rows / $per_page);
            if ($q->rowCount() > 0) {
                $res = $q->fetchAll(PDO::FETCH_ASSOC);
                http_response_code(200);
                echo json_encode([
                    'pages' => $pages + 1,
                    'page' => $page,
                    'per_page' => $per_page,
                    'data' => $res
                ]);
            } else {
                http_response_code(200);
                echo json_encode([
                    'pages' => $pages,
                    'page' => $page,
                    'per_page' => $per_page,
                    'data' => null
                ]);
            }
        } else {
            http_response_code(200);
            echo json_encode(['message' => 'No projects found']);
        }
    }
}
