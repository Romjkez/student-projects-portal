<?php session_start();
if (isset($_SESSION['email'])) {
    header('Location: /');
} else {
    require_once 'classes/reg.php';
    $reg = new Reg();
    $reg->outputForm();

    if (isset($_POST['submit'])) {
        if ($reg->verifyForm() == true && $reg->isRegistered() == false) {
            $reg->sendForm();
        }
    }
}