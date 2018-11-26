<?php session_start();
if (isset($_SESSION['email'])) {
    if ($_SESSION['usergroup'] == 2) {
        if (isset($_GET['createproject'])) {
            
        } else {
            echo '<a href="?createproject">Создать проект</a>';
        }
    } else {

    }
} else header('Location: login.php');