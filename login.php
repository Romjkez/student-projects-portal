<?php session_start();
if (isset($_SESSION['email'])) {
    header('Location: /');
} else {
    require_once 'classes/auth.php';
    $login = new Auth();
    if (isset($_POST['submit'])) {
        if ($login->isRegistered() == true && $login->verifyUser() == true) {
            $login->loginUser($_POST['email']);
        }
    } else $login->outputForm();

}
