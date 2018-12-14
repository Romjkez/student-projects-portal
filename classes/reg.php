<?php

final class Reg
{
    private $oauth_vk_link;
    private $oauth_yandex_link;

    function __construct()
    {
        $this->printHeader();
        $this->oauth_yandex_link = 'https://oauth.yandex.ru/authorize?response_type=code&client_id=9720b31b284b4caf812835ac8e672eb3&redirect_uri=http://new.std-247.ist.mospolytech.ru/oauth/yandex_auth.php';
        $this->oauth_vk_link = 'https://oauth.vk.com/authorize?client_id=6716519&display=page&redirect_uri=http://new.std-247.ist.mospolytech.ru/oauth/vk_auth.php&scope=email&response_type=code&v=5.92';
    }

    public function outputForm()
    {
        echo '<div style="text-align: center"><h1>Регистрация</h1><p>В будущем форма регистрации будет различаться у заказчиков и исполнителей</p></div>';
        if (isset($_GET['email']) && isset($_GET['name']) && isset($_GET['surname'])) {
            echo "<div style='text-align: center'>
        <form action='" . $_SERVER["REQUEST_URI"] . "' method='post'>
            <div style='border:1px solid orange;width:fit-content;margin:0 auto'><label for=''>Я исполнитель</label><input type='radio' name='usergroup' value='1' required><br>
            <label for=''>Я заказчик</label><input id='usergroupselector' type='radio' name='usergroup' value='2'></div><br>
            <input value='" . $_GET['name'] . "' type='text' name='name' placeholder='Ваше имя' minlength='2' required autofocus maxlength='50'><br>
            <input value='" . $_GET['surname'] . "' type='text' name='surname' placeholder='Ваша фамилия' minlength='2' required maxlength='50'><br>
            <input type='text' name='middlename' placeholder='Ваше отчество' maxlength='50'><br>
            <input value='" . $_GET['email'] . "' type='email' name='email' placeholder='Ваш email' minlength='2' maxlength='100' required><br>
            <input type='password' name='pass' placeholder='Ваш пароль' minlength='6' required><br>
            <input type='password' name='pass_confirm' placeholder='Введите пароль ещё раз' minlength='6' required><br>
            <input value='" . $_GET['phone'] . "' type='tel' name='tel' placeholder='Ваш телефон'><br>
            <input type='text' name='std_group' maxlength='20' placeholder='Учебная группа'><br>
            <input value='" . $_GET['avatar'] . "' type='text' name='avatar' maxlength='255' placeholder='Ссылка на ваше фото'><br>
            <textarea placeholder='О себе' name='description'></textarea><br>
            <button type='submit' name='submit'>Зарегистрироваться</button>
        </form>
        <div style='background:#eee;padding:5px;'>Для завершения регистрации, выберите вашу роль, отметив чекбокс в начале формы</div>
        <br>
        <b>Авторизация через VK/Яндекс</b><br>
        <a href='" . $this->oauth_vk_link . "'><img src='../assets/img/vk_icon.svg' width=50 height=50 alt=''></a>
        <a href=\"" . $this->oauth_yandex_link . "\"><img src=\"../assets/img/yandex_icon.svg\" width=\"50\" height=\"50\" alt=\"\"></a>
        </div>";
        } else echo "
        <div style='text-align: center'><form action='" . $_SERVER["REQUEST_URI"] . "' method='post'>
            <label for=''>Я исполнитель</label><input type='radio' name='usergroup' value='1' required><br>
            <label for=''>Я заказчик</label><input type='radio' name='usergroup' value='2'><br>
            <input type='text' name='name' placeholder='Ваше имя' minlength='2' required autofocus maxlength='50'><br>
            <input type='text' name='surname' placeholder='Ваша фамилия' minlength='2' required maxlength='50'><br>
            <input type='text' name='middlename' placeholder='Ваше отчество' maxlength='50'><br>
            <input type='email' name='email' placeholder='Ваш email' minlength='2' maxlength='100' required><br>
            <input type='password' name='pass' placeholder='Ваш пароль' minlength='6' required><br>
            <input type='password' name='pass_confirm' placeholder='Введите пароль ещё раз' minlength='6' required><br>
            <input type='tel' name='tel' placeholder='Ваш телефон'><br>
            <input type='text' name='std_group' maxlength='20' placeholder='Учебная группа'><br>
            <input type='text' name='avatar' maxlength='255' placeholder='Ссылка на ваше фото'><br>
            <textarea placeholder='О себе' name='description'></textarea><br>
            <button type='submit' name='submit'>Зарегистрироваться</button>
        </form>
        <br>
        <b>Авторизация через VK/Яндекс</b><br>
        <a href='" . $this->oauth_vk_link . "'><img src='../assets/img/vk_icon.svg' width=50 height=50 alt=''></a>
        <a href=\"" . $this->oauth_yandex_link . "\"><img src=\"../assets/img/yandex_icon.svg\" width=\"50\" height=\"50\" alt=\"\"></a>
        </div>";
    }

    public function verifyForm()
    {
        if (isset($_POST['name']) && iconv_strlen($_POST['surname']) > 1 && iconv_strlen($_POST['email']) > 1 && iconv_strlen($_POST['pass']) > 5 && iconv_strlen($_POST['pass_confirm']) > 5) {
            if (iconv_strlen($_POST['name']) < 50 && iconv_strlen($_POST['surname']) < 50 && iconv_strlen($_POST['middlename']) < 50 && iconv_strlen($_POST['pass']) > 5) {
                if ($_POST['pass'] === $_POST['pass_confirm'])
                    return true;
                else {
                    echo '<div style=\'background:#eee;padding:5px;\'>Введённые вами пароли не совпадают.</div>';
                    return false;
                }
            } else {
                echo '<div style=\'background:#eee;padding:5px;\'>Имя/фамилия/отчество не должны быть длиннее 50 символов, а пароль - не менее 6 символов.</div>';
                return false;
            }
        } else {
            echo '<div style=\'background:#eee;padding:5px;\'>Пожалуйста, заполните все обязательные поля.</div>';
            return false;
        }
    }

    public function isRegistered()
    {
        $check = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/api/user/isregistered.php');
        curl_setopt($check, CURLOPT_POSTFIELDS, $_POST);
        curl_setopt($check, CURLOPT_RETURNTRANSFER, true);
        $response = json_decode(curl_exec($check), true);
        curl_close($check);
        if ($response['message'] == "true") {
            echo 'Пользователь с таким email уже зарегистрирован.';
            return true;
        } else return false;
    }

    public function sendForm()
    {
        $data = $this->prepareData($_POST);
        $data['api_key'] = 'android';
        $ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/api/user/add.php');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);
        if ($response['message'] == "true") $this->loginUser();
    }

    private function loginUser()
    {
        session_start();
        $_SESSION['name'] = htmlspecialchars(trim($_POST['name']));
        $_SESSION['surname'] = htmlspecialchars(trim($_POST['surname']));
        $_SESSION['email'] = htmlspecialchars($_POST['email']);
        $_SESSION['usergroup'] = htmlspecialchars(trim($_POST['usergroup']));
        $_SESSION['avatar'] = $this->setAvatar();
        echo 'Регистрация прошла успешно! Перенаправляем на главную...
            <script>setTimeout(function(){window.location.href="/"},1500)</script>';
    }

    private function setAvatar()
    {
        return (iconv_strlen($_POST['avatar']) < 4) ? 'assets/img/defaultAvatar.png' : $_POST['avatar'];
    }

    function prepareData($array)
    {
        $data = [];
        foreach ($array as $key => $value) {
            $data[$key] = $value;
        }
        return $data;
    }

    private function printHeader()
    {
        echo '<head>
                    <title>Регистрация</title>
                    <meta charset="UTF-8">
                    <style>body{font-family: Arial,Helvetica,sans-serif }</style>
              </head>
            ';
    }
}