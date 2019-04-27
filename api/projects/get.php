<?php
require_once '../headers.php';

$status0 = 0;
$status1 = 1;
$status2 = 2;
$status3 = 3;
/*ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);*/

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (is_numeric($_GET['id']) && !isset($_GET['sort'])) {
        getProjectById();
    } else if (is_numeric($_GET['status']) && !isset($_GET['curator']) && ($_GET['page']) > 0 && $_GET['per_page'] > 0) {
        getProjectsByStatus();
    } else if (isset($_GET['curator']) && !isset($_GET['status']) && ($_GET['page']) > 0 && $_GET['per_page'] > 0) {
        getProjectsByCurator();
    } else if (isset($_GET['curator']) && is_numeric($_GET['status']) && ($_GET['page']) > 0 && $_GET['per_page'] > 0) {
        getProjectByCuratorAndStatus($_GET['curator'], $_GET['status']);
    } else if (is_numeric($_GET['user'])) {
        getUserProjects();
    } else if (isset($_GET['title'])) {
        getProjectsByTitle();
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Specified GET parameters are incorrect']);
    }

} else if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
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
        require_once '../file/get.php';
        $files = get($id);
        if ($files) {
            $res->files = $files;
        } else {
            $res->files = null;
        }
        http_response_code(200);
        echo json_encode($res);
    } else {
        http_response_code(200);
        echo json_encode(['message' => 'No projects found']);
    }
}

function getProjectsByStatus()
{
    $status = preg_replace('/[^0-9]/', '', $_GET['status']);
    require_once '../file/get.php';
    require_once '../../database.php';
    $db = new Database();
    $page = (int)$_GET['page'];
    $per_page = (int)$_GET['per_page'];
    $q = $db->connection->prepare("SELECT * FROM projects_new WHERE status IN(?,?) ORDER BY " . sortResult() . " LIMIT ? OFFSET ?");
    $infoQuery = $db->connection->prepare('SELECT id FROM projects_new WHERE status IN(?,?)');

    if (iconv_strlen($status) == 2) {
        $infoQuery->bindValue(1, $status[0]);
        $infoQuery->bindValue(2, $status[1]);
        $q->bindValue(1, $status[0]);
        $q->bindValue(2, $status[1]);
    } else {
        $infoQuery->bindValue(1, $status);
        $infoQuery->bindValue(2, $status);
        $q->bindValue(1, $status);
        $q->bindValue(2, $status);
    }
    $q->bindValue(3, $per_page, PDO::PARAM_INT);
    $q->bindValue(4, ($page - 1) * $per_page, PDO::PARAM_INT);

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
            $files = get($obj->id);
            if ($files) {
                $obj->files = $files;
            } else $obj->files = null;
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
        $q = $db->connection->prepare("SELECT id FROM users WHERE email=?");
        $q->bindParam(1, $_GET['curator']);
        $q->execute();
        if ($q->rowCount() > 0) {
            $curatorId = (int)$q->fetch()[0];
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
    require_once '../file/get.php';

    $db = new Database();
    $infoQuery = $db->connection->prepare('SELECT * FROM projects_new WHERE curator=:curator');
    $infoQuery->bindParam(':curator', $curatorId);
    $infoQuery->execute();
    $rows = $infoQuery->rowCount();
    $page = (int)$_GET['page'];
    $per_page = (int)$_GET['per_page'];

    $q = $db->connection->prepare("SELECT * FROM projects_new WHERE curator=:curator ORDER BY " . sortResult() . " LIMIT :per_page OFFSET :page");
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
            $files = get($obj->id);
            if ($files) {
                $obj->files = $files;
            } else $obj->files = null;
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
    $status = preg_replace('/[^0-9]/', '', $_GET['status']);
    require_once '../../database.php';
    require_once '../file/get.php';
    $page = (int)$_GET['page'];
    $per_page = (int)$_GET['per_page'];
    $db = new Database();

    if (is_numeric($curator)) {
        $infoQuery = $db->connection->prepare("SELECT * FROM projects_new WHERE curator=? AND status IN(?,?)");
        $q = $db->connection->prepare("SELECT * FROM projects_new WHERE curator=? AND status IN(?,?) ORDER BY " . sortResult() . " LIMIT ? OFFSET ?");
        $q->bindParam(1, $curator);
        $infoQuery->bindParam(1, $curator);

        if (iconv_strlen($status) == 2) {
            $infoQuery->bindValue(2, $status[0]);
            $infoQuery->bindValue(3, $status[1]);
            $q->bindValue(2, $status[0]);
            $q->bindValue(3, $status[1]);
        } else {
            $infoQuery->bindValue(2, $status);
            $infoQuery->bindValue(3, $status);
            $q->bindValue(2, $status);
            $q->bindValue(3, $status);
        }
        $q->bindValue(4, $per_page, PDO::PARAM_INT);
        $q->bindValue(5, ($page - 1) * $per_page, PDO::PARAM_INT);

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
                $files = get($obj->id);
                if ($files) {
                    $obj->files = $files;
                } else $obj->files = null;
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
        $q = $db->connection->prepare("SELECT id FROM users WHERE email=?");
        $q->bindParam(1, $_GET['curator']);
        $q->execute();
        if ($q->rowCount() > 0) {
            $curatorId = (int)$q->fetch()[0];
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
    require_once '../file/get.php';
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
                    $files = get($elem->id);
                    if ($files) {
                        $elem->files = $files;
                    } else $elem->files = null;
                    array_push($result['active_projects'], $elem);
                }
            }
            if (count($result['active_projects']) == 0) $result['active_projects'] = null;
            foreach ($finished as $item) {
                $projects->bindValue(1, $item);
                $projects->execute();
                $elem = $projects->fetchObject();
                $elem->members = fillMembers($elem->members);
                $elem->curator = getCurator($elem->curator);
                $files = get($elem->id);
                if ($files) {
                    $elem->files = $files;
                } else $elem->files = null;
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

function getProjectsByTitle()
{
    require_once '../../database.php';
    require_once '../file/get.php';

    $db = new Database();
    $q = $db->connection->prepare("SELECT * FROM projects_new WHERE projects_new.title LIKE LOWER(?) AND projects_new.status!=0 AND projects_new.status!=3");
    $title = $_GET['title'];
    $q->bindValue(1, "%$title%");
    $q->execute();
    if ($q->rowCount() > 0) {
        $result = [];
        for ($i = 0; $i < $q->rowCount(); $i++) {
            $obj = $q->fetchObject();
            $obj->curator = getCurator($obj->curator);
            $files = get($obj->id);
            if ($files) {
                $obj->files = $files;
            } else $obj->files = null;
            $result[$i] = $obj;
        }
        http_response_code(200);
        echo json_encode($result);
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

function sortResult()
{
    if (isset($_GET['sort'])) {
        switch (strtolower($_GET['sort'])) {
            case '-finish_date':
                return 'finish_date DESC';
                break;
            case 'finish_date':
                return 'finish_date';
                break;
            case '-deadline':
                return 'deadline DESC';
                break;
            case 'deadline':
                return 'deadline';
                break;
            case 'id':
                return 'id';
                break;
            case '-id':
                return 'id DESC';
                break;
            // case '-occupancy': return ''; break;
            // case 'occupancy': return ''; break;
            default:
                return 'id';
        }
    } else return 'id';
}
