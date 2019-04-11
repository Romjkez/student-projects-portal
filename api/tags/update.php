<?php
require_once '../headers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['category'])) {

    }

} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}
