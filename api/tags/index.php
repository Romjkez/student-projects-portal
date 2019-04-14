<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS, PUT, GET, DELETE');
header('Access-Control-Expose-Headers: X-Auth-Token');
header('Access-Control-Allow-Headers: X-Auth-Token, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'add.php';
    add();
} else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    require_once 'update.php';
    update();
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once 'get.php';
    get();
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    require_once 'delete.php';
    delete();
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}
