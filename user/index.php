<?php session_start();
//todo для заказчиков отображение профиля будет другое т.к. surname - название организации
if (isset($_SESSION['email'])) {


    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $user = json_decode(@file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/user/get.php?id=' . $_GET['id'] . '&api_key=android', false), true);
        $user['avatar'] = setAvatar($user['avatar']);
        $user['usergroup'] = setUserGroup($user['usergroup']);
        $user['phone'] = setUserPhone($user['phone']);
        $user['description'] = setUserPhone($user['description']);
        printHeader($user['name'], $user['surname']);
        echo '<h1>' . $user['surname'] . ' ' . $user['name'] . ' ' . $user['middle_name'] . '</h1>';
        echo '<img src="' . $user['avatar'] . '" alt="Фотография" width="200" height="200"><br><br>';
        echo $user['usergroup'] . '<br>Контактный телефон: ' . $user['phone'] . '<br>';
        echo 'О себе: ' . $user['description'];

    } else {
        header('Location: /404.php');
    }
} else header('Location: /login.php');
function printHeader($name, $surname)
{
    echo '<head><title>' . $surname . ' ' . $name . ' | Профиль</title><meta charset="UTF-8"><style>body{font-family: Arial,sans-serif}</style></head>';
}

function setAvatar($link)
{
    return (iconv_strlen($link) < 4) ? '/assets/img/defaultAvatar.png' : $link;
}

function setUserGroup($groupId)
{
    switch ($groupId) {
        case 1:
            return '<b style="color:#577bff">Исполнитель</b>';
            break;
        case 2:
            return '<b style="color:#ffae59">Заказчик</b>';
            break;
        case 3:
            return '<b style="color:#f55351">Администратор</b>';
            break;
        default:
            return '';
    }
}

function setUserPhone($phone)
{
    return (iconv_strlen($phone) < 5) ? '<i>информация отсутствует</i>' : $phone;
}