<?php
require_once '../headers.php';
/*ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);*/
$status0 = 0;
$status1 = 1;
$status2 = 2;
$status3 = 3;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (is_numeric($_GET['id'])) {
        getProjectById();
    } else if (is_numeric($_GET['status']) && !isset($_GET['curator']) && ($_GET['page']) > 0 && $_GET['per_page'] > 0) {
        getProjectsByStatus();
    } else if (isset($_GET['curator']) && !isset($_GET['status']) && ($_GET['page']) > 0 && $_GET['per_page'] > 0) {
        getProjectsByCurator();
    } else if (isset($_GET['curator']) && is_numeric($_GET['status']) && ($_GET['page']) > 0 && $_GET['per_page'] > 0) {
        getProjectByCuratorAndStatus($_GET['curator'], $_GET['status']);
    } else if (is_numeric($_GET['user'])) {
        getUserProjects();
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
    $id = $_GET['id'];
    require_once '../../database.php';
    $db = new Database();
    $q = $db->connection->prepare("SELECT * FROM projects_new WHERE id=?");
    $q->bindParam(1, $id);
    $q->execute();
    if ($q->rowCount() > 0) {
        $res = $q->fetchObject();
        $res->members = fillMembers($res->members);
        $res->curator = getCurator($res->curator);
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
    $status0 = 0;
    $status1 = 1;
    $status2 = 2;
    $status3 = 3;
    require_once '../../database.php';
    $db = new Database();
    $page = (int)$_GET['page'];
    $per_page = (int)$_GET['per_page'];
    if ($status == 30) {
        $infoQuery = $db->connection->prepare('SELECT * FROM projects_new WHERE status=:status0 OR status=:status3');
        $infoQuery->bindParam(':status0', $status0);
        $infoQuery->bindParam(':status3', $status3);

        $q = $db->connection->prepare("SELECT * FROM projects_new WHERE status=:status0 OR status=:status3 ORDER BY id DESC LIMIT :per_page OFFSET :page");
        $q->bindParam(':status0', $status0);
        $q->bindParam(':status3', $status3);

    } else if ($status == 12) {
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
        $res = [];
        for ($i = 0; $i < $q->rowCount(); $i++) {
            $obj = $q->fetchObject();
            $obj->members = fillMembers($obj->members);
            $obj->curator = getCurator($obj->curator);
            $res[$i] = $obj;
        }
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
        $res = [];
        for ($i = 0; $i < $q->rowCount(); $i++) {
            $obj = $q->fetchObject();
            $obj->members = fillMembers($obj->members);
            $obj->curator = getCurator($obj->curator);
            $res[$i] = $obj;
        }
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
            'data' => null
        ]);
    }
}

function getProjectByCuratorAndStatus($curator, $status)
{
    $status = (int)preg_replace('/[^0-9]/', '', $status); // =0 if GET[status] does not contain numbers
    $status0 = 0;
    $status1 = 1;
    $status2 = 2;
    $status3 = 3;
    require_once '../../database.php';
    $page = (int)$_GET['page'];
    $per_page = (int)$_GET['per_page'];
    $db = new Database();
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
            $res = [];
            for ($i = 0; $i < $q->rowCount(); $i++) {
                $obj = $q->fetchObject();
                $obj->members = fillMembers($obj->members);
                $obj->curator = getCurator($obj->curator);
                $res[$i] = $obj;
            }
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

function getUserProjects()
{
    require_once '../../database.php';
    $db = new Database();
    $q = $db->connection->prepare("SELECT active_projects,finished_projects FROM users WHERE id=:user");
    $q->bindParam(':user', $_GET['user']);
    $q->execute();
    if ($q->rowCount() > 0) {
        $result = $q->fetchObject();
        $active = [];
        $finished = [];
        if ($result->active_projects !== null) {
            $active = explode(',', $result->active_projects);
        }
        if ($result->finished_projects !== null) {
            $finished = explode(',', $result->finished_projects);
        }
        if (count($active) > 0 || count($finished) > 0) {
            $result = [
                'active_projects' => [],
                'finished_projects' => []
            ];
            $projects = $db->connection->prepare("SELECT * FROM projects_new WHERE id=?");
            foreach ($active as $item) {
                $projects->bindValue(1, $item);
                $projects->execute();
                $elem = $projects->fetchObject();
                if ($elem) {
                    $elem->members = fillMembers($elem->members);
                    $elem->curator = getCurator($elem->curator);
                    array_push($result['active_projects'], $elem);
                }
            }
            json_decode($result['active_projects']);
            if (count($result['active_projects']) == 0) $result['active_projects'] = null;
            foreach ($finished as $item) {
                $projects->bindValue(1, $item);
                $projects->execute();
                $elem = $projects->fetchObject();
                $elem->members = fillMembers($elem->members);
                $elem->curator = getCurator($elem->curator);
                array_push($result['finished_projects'], $elem);
            }

            if (count($result['finished_projects']) == 0) $result['finished_projects'] = null;

            echo json_encode($result);
        } else {
            echo json_encode(['active_projects' => null, 'finished_projects' => null, 'code1']);
        }
    } else {
        echo json_encode(['active_projects' => null, 'finished_projects' => null, 'code2']);
    }
}

function fillMembers($members)
{
    $members = json_decode($members);
    $db = new Database();
    for ($i = 0; $i < count($members); $i++) {
        foreach ($members[$i] as $key => &$value) {
            if ($value != 0) {
                $user = $db->connection->prepare("SELECT id,name,surname,middle_name,email,phone,stdgroup,description,avatar,usergroup,active_projects,finished_projects FROM users WHERE users.id=:id");
                $user->bindParam(':id', $value);
                $user->execute();
                $value = $user->rowCount() > 0 ? $user->fetchObject() : 0;
            }
        }
    }
    return $members;
}

function getCurator($curatorId)
{
    $db = new Database();
    $q = $db->connection->prepare("SELECT id,name,surname,middle_name,email,phone,stdgroup,description,avatar,usergroup,active_projects,finished_projects FROM users WHERE users.id=:id");
    $q->bindParam(':id', $curatorId);
    $q->execute();
    return $q->rowCount() > 0 ? $q->fetchObject() : $curatorId;
}
