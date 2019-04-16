<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

function upload()
{
    /**
     * todo Загрузка невозможна
     * Warning: move_uploaded_file(/home/std/programma_KONFERENTsII_23_04_2019.pdf): failed to open stream: Permission denied in /home/std/new/api/file/upload.php on line 20
     * Warning: move_uploaded_file(): Unable to move '/tmp/phpC9yPeh' to '/home/std/programma_KONFERENTsII_23_04_2019.pdf' in /home/std/new/api/file/upload.php on line 20
     */
    $files = [];
    foreach ($_FILES as &$FILE) {
        if ($FILE['size'] < 200 && $FILE['error'] === 0) {
            http_response_code(400);
            echo json_encode(['message' => 'Размер файла слишком мал(<200B)']);
        } else if ($FILE['size'] > 2097152 && $FILE['error'] === 0) {
            http_response_code(400);
            echo json_encode(['message' => 'Размер файла слишком велик(>2Mb)']);
        } else array_push($files, $FILE);
    }
    if (!empty($files)) {
        foreach ($files as $file) {
            $result = move_uploaded_file($file['tmp_name'], '/home/std/' . $file['name']);
            echo json_encode(['result' => $result]);
        }
    }
}
