<?php
require_once '../headers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['deadline']) && isset($_POST['finish_date']) && isset($_POST['title']) && isset($_POST['description']) && isset($_POST['members']) && is_numeric($_POST['curator']) && isset($_POST['tags'])) {
        if (strtotime($_POST['deadline']) < strtotime($_POST['finish_date'])) {
            $tags = prepareTags($_POST['tags']);
            require_once '../../database.php';
            $db = new Database();
            $q = $db->connection->prepare("INSERT INTO `projects_new` (`id`, `title`, `description`, `members`, `deadline`,`finish_date`, `curator`, `tags`, `status`,`adm_comment`,`files`,`avatar`) VALUES (NULL,?,?,?,?,?,?,?,0,'',null,?)");
            $q->bindParam(1, $_POST['title']);
            $q->bindParam(2, $_POST['description']);
            $q->bindParam(3, $_POST['members']);
            $q->bindParam(4, $_POST['deadline']);
            $q->bindParam(5, $_POST['finish_date']);
            $q->bindParam(6, $_POST['curator']);
            $q->bindParam(7, $tags);
            $q->bindParam(8, $_POST['avatar']);
            $result = $q->execute();
            if ($result == true) {
                http_response_code(201);
                echo json_encode(['message' => "true"]);
                logCreation($tags);
            } else {
                http_response_code(200);
                echo json_encode(['message' => "false"]);
            }
        } else {
            http_response_code(200);
            echo json_encode(['message' => "Крайняя дата записи должна быть раньше, чем дата окончания проекта"]);
        }
    } else {
        echo json_encode(['message' => 'Specify all the necessary params']);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}
function logCreation($tags)
{
    date_default_timezone_set("Europe/Moscow");
    $newArr = [];
    foreach ($_POST as $key => $value) {
        if ($value != '') {
            $newArr[$key] = $value;
        } else $newArr[$key] = 'NULL';
    }
    $newlog = "\n" . 'CREATE:' . date('Y-m-d[H:i:s]') . ' | TITLE:' . $newArr['title'] . ' | DEADLINE:' . $newArr['deadline'] . '| FINISH:' . $newArr['finish_date'] . ' | CURATOR:' . $newArr['curator'] . ' | TAGS:' . $tags;
    file_put_contents('../../log/projects.txt', $newlog, FILE_APPEND);
}

function prepareTags($tags)
{
    $tags = explode(',', $tags, 8);
    $result = [];
    for ($i = 0; $i < count($tags); $i++) {
        if (count($tags[$i]) > 0) {
            $result[$i] = $tags[$i];
        }
    }
    return implode(',', $result);
}
