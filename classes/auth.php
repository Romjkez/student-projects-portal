<?php

final class Auth
{
    function verifyUser()
    {
        if (isset($_POST['email']) && isset($_POST['pass'])) {
            $check = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/api/user/verify.php');
            curl_setopt($check, CURLOPT_POSTFIELDS, $_POST);
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
        <b>Авторизация через VK</b><br>
        <a href=\'https://oauth.vk.com/authorize?client_id=6716519&display=page&redirect_uri=http://new.std-247.ist.mospolytech.ru/callback.php&scope=email&response_type=code&v=5.92\'><img src=\'../assets/img/vk_icon.svg\' width=50 height=50 alt=\'\'></a>
        </div>';
    }

    function loginUser($email)
    {
        $result = json_decode(file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/user/get.php?email=' . $email), true);
        session_start();
        $_SESSION['name'] = htmlspecialchars($result['name']);
        $_SESSION['surname'] = htmlspecialchars($result['surname']);
        $_SESSION['email'] = htmlspecialchars($result['email']);
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
}