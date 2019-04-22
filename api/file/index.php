<?php
require_once '../headers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (is_numeric($_REQUEST['project_id'])) {
        require_once 'upload.php';
        upload();
    } else json_encode(['message' => 'Specify project_id parameter']);
} else if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once 'get.php';
    get();
}
