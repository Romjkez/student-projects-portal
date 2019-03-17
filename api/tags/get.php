<?php
require_once '../headers.php';
require_once '../../database.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $db = new Database();
    if (isset($_GET['categories'])) {
        $q = $db->connection->prepare('SELECT category FROM tags');
        $q->execute();
        $rows = $q->rowCount();
        if ($rows > 0) {
            $result = '';
            for ($i = 0; $i < $rows; $i++) {
                $result .= $q->fetch(PDO::FETCH_NUM)[0];
                if ($i !== $rows - 1) $result .= ',';
            }
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(200);
            echo json_encode(['message' => 'No categories found']);
        }
    } else if (isset($_GET['values'])) {
        $q = $db->connection->prepare('SELECT value FROM tags');
        $q->execute();
        $rows = $q->rowCount();
        if ($rows > 0) {
            $result = '';
            for ($i = 0; $i < $rows; $i++) {
                $result .= $q->fetch(PDO::FETCH_NUM)[0];
                if ($i !== $rows - 1) $result .= ',';
            }
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(200);
            echo json_encode(['message' => 'No tags found']);
        }
    } else {
        $q = $db->connection->prepare('SELECT category,value FROM tags');
        $q->execute();
        if ($q->rowCount() > 0) {
            $result = $q->fetchAll(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(200);
            echo json_encode(['message' => 'No tags found']);
        }
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}
