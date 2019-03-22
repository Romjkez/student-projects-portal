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
    } else if (is_numeric($_GET['worker']) && isset($_GET['status']) && ($_GET['page']) > 0 && $_GET['per_page'] > 0) {
        getProjectsByWorkerAndStatus();
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
    $q = $db->connection->prepare("SELECT * FROM projects_new WHERE id=?");
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
    $page = (int)$_GET['page'];
    $per_page = (int)$_GET['per_page'];
    if ($status == 30) {
        $status0 = 0;
        $status3 = 3;
        $infoQuery = $db->connection->prepare('SELECT * FROM projects_new WHERE status=:status0 OR status=:status3');
        $infoQuery->bindParam(':status0', $status0);
        $infoQuery->bindParam(':status3', $status3);

        $q = $db->connection->prepare("SELECT * FROM projects_new WHERE status=:status0 OR status=:status3 ORDER BY id DESC LIMIT :per_page OFFSET :page");
        $q->bindParam(':status0', $status0);
        $q->bindParam(':status3', $status3);

    } else if ($status == 12) {
        $status1 = 1;
        $status2 = 2;
        $infoQuery = $db->connection->prepare('SELECT * FROM projects_new WHERE status=:status1 OR status=:status2');
        $infoQuery->bindParam(':status1', $status1);
        $infoQuery->bindParam(':status2', $status2);

        $q = $db->connection->prepare("SELECT * FROM projects_new WHERE status=:status1 OR status=:status2 ORDER BY id DESC LIMIT :per_page OFFSET :page");
        $q->bindParam(':status1', $status1);
        $q->bindParam(':status2', $status2);
    } else {
        $infoQuery = $db->connection->prepare('SELECT * FROM projects_new WHERE status=:status');
        $infoQuery->bindParam(':status', $status);

        $q = $db->connection->prepare("SELECT * FROM projects_new WHERE status=:status ORDER BY id DESC LIMIT :per_page OFFSET :page");
        $q->bindParam(':status', $status);

    }
    $q->bindValue(':per_page', $per_page, PDO::PARAM_INT);
    $q->bindValue(':page', ($page - 1) * $per_page, PDO::PARAM_INT);

    $infoQuery->execute();
    $rows = $infoQuery->rowCount();
    $pages = ceil($rows / $per_page);

    $q->execute();
    if ($q->rowCount() > 0) {
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        http_response_code(200);
        echo json_encode([
            'pages' => $pages,
            'page' => $page,
            'per_page' => $per_page,
            'data' => $res,
        ]);
    } else {
        http_response_code(200);
        echo json_encode([
            'pages' => $pages,
            'page' => $page,
            'per_page' => $per_page,
            'data' => null,
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
    $infoQuery = $db->connection->prepare('SELECT * FROM projects_new WHERE curator=:curator');
    $infoQuery->bindParam(':curator', $curatorId);
    $infoQuery->execute();
    $rows = $infoQuery->rowCount();
    $page = (int)$_GET['page'];
    $per_page = (int)$_GET['per_page'];

    $q = $db->connection->prepare("SELECT * FROM projects_new WHERE curator=:curator ORDER BY id DESC LIMIT :per_page OFFSET :page");
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
            'pages' => $pages,
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
    $page = (int)$_GET['page'];
    $per_page = (int)$_GET['per_page'];
    $db = new Database();
    $status0 = 0;
    $status1 = 1;
    $status2 = 2;
    $status3 = 3;
    if (is_numeric($curator)) {
        if ($status == 30) {
            $infoQuery = $db->connection->prepare("SELECT * FROM projects_new WHERE curator=:curator AND (status=:status0 OR status=:status3)");
            $infoQuery->bindParam(':status0', $status0);
            $infoQuery->bindParam(':status3', $status3);

            $q = $db->connection->prepare("SELECT * FROM projects_new WHERE curator=:curator AND (status=:status0 OR status=:status3) ORDER BY id DESC LIMIT :per_page OFFSET :page");
            $q->bindParam(':status0', $status0);
            $q->bindParam(':status3', $status3);
        } else if ($status == 12) {
            $infoQuery = $db->connection->prepare("SELECT * FROM projects_new WHERE curator=:curator AND (status=:status1 OR status=:status2)");
            $infoQuery->bindParam(':status1', $status1);
            $infoQuery->bindParam(':status2', $status2);

            $q = $db->connection->prepare("SELECT * FROM projects_new WHERE curator=:curator AND (status=:status1 OR status=:status2) ORDER BY id DESC LIMIT :per_page OFFSET :page");
            $q->bindParam(':status1', $status1);
            $q->bindParam(':status2', $status2);
        } else {
            $infoQuery = $db->connection->prepare("SELECT * FROM projects_new WHERE curator=:curator AND status=:status");
            $infoQuery->bindParam(':status', $status);

            $q = $db->connection->prepare("SELECT * FROM projects_new WHERE curator=:curator AND status=:status ORDER BY id DESC LIMIT :per_page OFFSET :page");
            $q->bindParam(':status', $status);
        }
        $q->bindParam(':curator', $curator);
        $q->bindValue(':per_page', $per_page, PDO::PARAM_INT);
        $q->bindValue(':page', ($page - 1) * $per_page, PDO::PARAM_INT);
        $infoQuery->bindParam(':curator', $curator);

        $infoQuery->execute();
        $rows = $infoQuery->rowCount();

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
            getProjectByCuratorAndStatus($curatorId, $status);
        } else {
            http_response_code(200);
            echo json_encode(['message' => 'No projects found']);
        }
    }
}

function getProjectsByWorkerAndStatus()
{
    $status = (int)preg_replace('/[^0-9]/', '', $_GET['status']); // =0 if GET[status] does not contain numbers
    require_once '../../database.php';
    $page = (int)$_GET['page'];
    $per_page = (int)$_GET['per_page'];
    $db = new Database();
    $status0 = 0;
    $status1 = 1;
    $status2 = 2;
    $status3 = 3;
    if ($status == 30) {
        $infoQuery = $db->connection->prepare("SELECT * FROM projects_new WHERE curator=:curator AND (status=:status0 OR status=:status3)");
        $infoQuery->bindParam(':status0', $status0);
        $infoQuery->bindParam(':status3', $status3);
        // todo ДОБАВИТЬ ЮЗЕРУ ПОЛЕ АКТИВНЫЕ И ЗАВЕРШЕННЫЕ ПРОЕКТЫ
    }

}
