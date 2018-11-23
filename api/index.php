<?php
require_once '../database.php';
$db = new Database();
$db->isConnected();
if ($db->isConnected()) echo json_encode(['message' => 'API is available']);
else echo json_encode(['message' => 'API is unavailable']);
$db->disconnect();
