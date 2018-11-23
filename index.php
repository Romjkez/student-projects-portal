<?php session_start();

if (isset($_SESSION['name']) && (isset($_SESSION['surname']))) {
    require_once 'classes/auth.php';
    $auth = new Auth();
    if (isset($_POST['logout'])) {
        $auth->logoutUser();
    } else {
        echo 'Добро пожаловать, ' . $_SESSION['name'];
        echo '<h1>Контент для авторизованных пользователей!</h1>
    <form method="post" action="">
    <button type="submit" name="logout">Выйти</button>
    </form>';

    }
} else echo '<a href="login.php">Авторизуйтесь</a> или <a href="register.php">Зарегистрируйтесь</a>';







