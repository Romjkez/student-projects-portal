<?php
require_once '../headers.php';
// get archive project by id or curator

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id'])) {
        getProjectById();
    } else if (!isset($_GET['curator']) && ($_GET['page']) > 0 && $_GET['per_page'] > 0) {
        getProjects();
    } else if (isset($_GET['curator']) && ($_GET['page']) > 0 && $_GET['per_page'] > 0) {
        getProjectsByCurator();
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
    $q = $db->connection->prepare("SELECT * FROM projects_archieve WHERE id=?");
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

function getProjects()
{
    require_once '../../database.php';
    $db = new Database();
    $infoQuery = $db->connection->prepare('SELECT * FROM projects_archieve');
    $infoQuery->execute();
    $rows = $infoQuery->rowCount();
    $page = (int)$_GET['page'];
    $per_page = (int)$_GET['per_page'];

    $q = $db->connection->prepare("SELECT * FROM projects_archieve LIMIT :per_page OFFSET :page");
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
    $infoQuery = $db->connection->prepare('SELECT * FROM projects_archieve WHERE curator=:curator');
    $infoQuery->bindParam(':curator', $curatorId);
    $infoQuery->execute();
    $rows = $infoQuery->rowCount();
    $page = (int)$_GET['page'];
    $per_page = (int)$_GET['per_page'];

    $q = $db->connection->prepare("SELECT * FROM projects_archieve WHERE curator=:curator LIMIT :per_page OFFSET :page");
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


