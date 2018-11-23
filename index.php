<?php session_start();

if (isset($_SESSION['name']) && (isset($_SESSION['surname']) && isset($_SESSION['email']))) {
    require_once 'classes/auth.php';
    $auth = new Auth();
    if (isset($_POST['logout'])) {
        $auth->logoutUser();
    } else {
        if ($_SESSION['usergroup'] == '1') $usrgroup = 'Исполнитель';
        else if ($_SESSION['usergroup'] == '2') $usrgroup = 'Заказчик';
        else if ($_SESSION['usergroup'] == '3') $usrgroup = 'Администратор';
        echo 'Добро пожаловать, ' . $_SESSION['name'] . ' ' . $_SESSION['surname'];
        echo '<br><img src="' . $_SESSION['avatar'] . '" alt="" width=200 height=200>';
        echo '<br>Вы авторизованы как ' . $usrgroup;
        echo '<form method="post" action=""><button type="submit" name="logout">Выйти</button></form>';

    }
} else echo '<a href="login.php">Авторизуйтесь</a> или <a href="register.php">Зарегистрируйтесь</a>';







