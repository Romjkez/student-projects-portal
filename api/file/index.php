<?php
require_once '../headers.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'upload.php';
    upload();
} else if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once 'get.php';
    get();
}
