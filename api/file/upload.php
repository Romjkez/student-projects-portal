<?php
/*ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);*/

function upload()
{
    require_once '../../database.php';
    $db = new Database();
    // todo ПОМЕНЯТЬ ПУТЬ В ПРОДЕ
    $dst = '/uploaded/p_';
    $dir_template = $_SERVER['DOCUMENT_ROOT'] . $dst;
    $files = [];
    foreach ($_FILES as &$FILE) {
        if ($FILE['size'] < 200 && $FILE['error'] === 0) {
            http_response_code(400);
            return ['message' => 'Размер файла слишком мал(<200B)'];
        } else if ($FILE['size'] > 5242880 && $FILE['error'] === 0) {
            http_response_code(400);
            return ['message' => 'Размер файла слишком велик(>5Mb)'];
        } else array_push($files, $FILE);
    }
    if (!empty($files) && count($files) == 1) {
        foreach ($files as $index => $file) {
            if (file_exists($dir_template . $_REQUEST['project_id'] . '/' . $file['name'])) {
                http_response_code(422);
                $fileName = $file['name'];
                return ['message' => "«{$fileName}» уже существует в документах проекта"];
            } else {
                if (!is_dir($dir_template . $_REQUEST['project_id'] . '/')) {
                    mkdir($dir_template . $_REQUEST['project_id'] . '/');
                }
                $res = move_uploaded_file($file['tmp_name'], $dir_template . $_REQUEST['project_id'] . '/' . $file['name']);
                if ($res) {
                    $q = $db->connection->prepare("INSERT INTO `files`(`id`, `project_id`, `title`, `link`) VALUES (NULL,?,?,?)");
                    $q->bindValue(1, $_REQUEST['project_id']);
                    $q->bindValue(2, $file['name']);
                    $q->bindValue(3, $dst . $_REQUEST['project_id'] . '/' . $file['name']);
                    $isSuccessful = $q->execute();
                    if ($isSuccessful) {
                        http_response_code(201);
                        return ['message' => $dst . $_REQUEST['project_id'] . '/' . $file['name']];
                    } else {
                        http_response_code(200);
                        return ['message' => $q->errorInfo()[2]];
                    }
                } else return ['message' => 'Не удалось сохранить файл'];
            }
        }
    } else {
        http_response_code(400);
        return ['message' => 'Поддерживается загрузка только 1 файла за раз'];
    }
}
