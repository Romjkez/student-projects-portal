<?php session_start();
echo '<head><title>Портал подбора студенческих проектов</title><meta charset="utf-8"></head>';
// тут запрос к API на обновление списка проектов
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
        echo '<br>Вы авторизованы как ' . $usrgroup . '<br>';
        echo '<a href="cabinet/">Личный кабинет</a>';
        echo '<form method="post" action=""><button type="submit" name="logout">Выйти</button></form>';

    }
} else echo '<a href="login.php">Авторизуйтесь</a> или <a href="register.php">Зарегистрируйтесь</a><br><br>';


/*$members= json_decode($res['members']);
echo countPlaces($members);*/
$j = json_encode(
    [
        ['Дизайнер' => 12, 'PHP-разработчик' => 1, 'Frontend-разработчик' => 0],
        ['Дизайнер' => 123, 'PHP-разработчик' => 0, 'Frontend-разработчик' => 4]
    ]
);

$de = json_decode($j, true);
// считает количество занятых к количеству свободных мест
function countPlaces($decoded)
{
    $c = 0;
    $teamsCount = count($decoded);
    $places = 0;
    for ($i = 0; $i < $teamsCount; $i++) {
        foreach ($decoded[$i] as $value) {
            if ($value !== 0) {
                $c++;
            }
            $places++;
        }
    }
    return $c . '/' . $places;
}






