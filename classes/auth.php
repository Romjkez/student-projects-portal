<?php

final class Auth
{
    private $oauth_vk_link;
    private $oauth_yandex_link;

    function __construct()
    {
        $this->printHeader();
        $this->oauth_yandex_link = 'https://oauth.yandex.ru/authorize?response_type=code&client_id=9720b31b284b4caf812835ac8e672eb3&redirect_uri=http://new.std-247.ist.mospolytech.ru/oauth/yandex_auth.php';
        $this->oauth_vk_link = 'https://oauth.vk.com/authorize?client_id=6716519&display=page&redirect_uri=http://new.std-247.ist.mospolytech.ru/oauth/vk_auth.php&scope=email&response_type=code&v=5.92';
    }

    function verifyUser()
    {
        if ((iconv_strlen($_POST['email']) > 2) && (iconv_strlen($_POST['pass']) > 3)) {
            $data = $this->prepareData($_POST);
            $data['api_key'] = 'android';
            $check = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/api/user/verify.php');
            curl_setopt($check, CURLOPT_POSTFIELDS, $data);
            curl_setopt($check, CURLOPT_RETURNTRANSFER, true);
            $response = json_decode(curl_exec($check), true);
            curl_close($check);
            if ($response['message'] == 'true') {
                return true;
            } else {
                echo 'Вы ввели неверный пароль. Перепроверьте введенный пароль и попробуйте ещё раз.';
                return false;
            }
        } else {
            echo 'Не введён email или пароль.';
            return false;
        }
    }

    // checks user's password

    function isRegistered()
    {
        $check = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/api/user/isregistered.php');
        curl_setopt($check, CURLOPT_POSTFIELDS, $_POST);
        curl_setopt($check, CURLOPT_RETURNTRANSFER, true);
        $response = json_decode(curl_exec($check), true);
        curl_close($check);
        if ($response['message'] == 'true') {
            return true;
        } else {
            $this->outputForm();
            echo 'Пользователь с таким email не найден.';
            return false;
        }
    }

    function outputForm()
    {
        echo '<div style="text-align: center">
        <form action="' . $_SERVER["REQUEST_URI"] . '" method="post">
            <input placeholder="Email" type="email" name="email" required maxlength="50" autofocus><br>
            <input placeholder="Password" type="password" name="pass" required>
            <button name="submit" type="submit">Войти</button>
        </form>
        <br>
        <b>Авторизация через VK/Яндекс</b><br>
        <a href=\'' . $this->oauth_vk_link . '\'><img src=\'../assets/img/vk_icon.svg\' width="50" height="50" alt=\'\'></a>
        <a href="' . $this->oauth_yandex_link . '"><img src="../assets/img/yandex_icon.svg" width="50" height="50" alt=""></a>
        </div>';
    }

    function loginUser($email)
    {
        $result = json_decode(file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/user/get.php?api_key=android&email=' . $email), true);
        session_start();
        $_SESSION['email'] = htmlspecialchars($email);
        $_SESSION['name'] = htmlspecialchars($result['name']);
        $_SESSION['surname'] = htmlspecialchars($result['surname']);
        $_SESSION['usergroup'] = $result['usergroup'];
        $_SESSION['avatar'] = $this->setAvatar($result['avatar']);

        echo 'Вы успешно авторизовались. Сейчас вы будете перенаправлены на главную...';
        header('Location: /');
    }

    function logoutUser()
    {
        unset($_SESSION);
        session_destroy();
        session_unset();
        header('Location:/');
    }

    function setAvatar($link)
    {
        return (iconv_strlen($link) < 4) ? 'assets/img/defaultAvatar.png' : $link;
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
                    <title>Авторизация</title>
                    <meta charset="UTF-8">
              </head>';
    }
}