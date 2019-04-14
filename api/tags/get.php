<?php

function get()
{
    require_once '../../database.php';
    $db = new Database();
    if (isset($_GET['categories'])) {
        $q = $db->connection->prepare('SELECT category FROM tags ORDER BY category');
        $q->execute();
        $db->disconnect();
        $rows = $q->rowCount();
        if ($rows > 0) {
            $result = $q->fetchAll(PDO::FETCH_UNIQUE);
            http_response_code(200);
            $result = array_keys($result);
            echo json_encode($result);
        } else {
            http_response_code(200);
            echo json_encode(['message' => 'No categories found']);
        }
    } else if (isset($_GET['values'])) {
        $q = $db->connection->prepare('SELECT value FROM tags');
        $q->execute();
        $db->disconnect();
        $rows = $q->rowCount();
        if ($rows > 0) {
            $result = $q->fetchAll(PDO::FETCH_UNIQUE);
            http_response_code(200);
            $result = array_keys($result);
            echo json_encode($result);
        } else {
            http_response_code(200);
            echo json_encode(['message' => 'No tags found']);
        }
    } else {
        $q = $db->connection->prepare('SELECT id,category,value FROM tags ORDER BY category');
        $q->execute();
        $db->disconnect();
        $rows = $q->rowCount();
        if ($rows > 0) {
            $result = $q->fetchAll(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(200);
            echo json_encode(['message' => 'No tags found']);
        }
    }
}
