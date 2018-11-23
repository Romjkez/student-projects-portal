<?php
if (isset($_GET['code'])) {
    $clientId = '6716519';
    $clientSecret = 'CPK9OYPQAceSCyhluAbG';
    $redirectUri = urlencode('http://new.std-247.ist.mospolytech.ru/callback.php');

    $params = [
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'code' => $_GET['code'],
        'redirect_uri' => $redirectUri
    ];
    if (!$content = @file_get_contents('https://oauth.vk.com/access_token?client_id=' . $clientId . '&client_secret=' . $clientSecret . '&redirect_uri=' . $redirectUri . '&code=' . $_GET['code'])) {
        $error = error_get_last();
        echo 'Ключ авторизации устарел. Повторите попытку авторизации.';
    } else {
        $response = json_decode($content);
        if (isset($response->error)) {
            echo('При получении токена произошла ошибка. Попробуйте авторизоваться ещё раз. ');
        } else {
            $token = $response->access_token;
            $userId = $response->user_id;
            $email = $response->email;

            $headers = stream_context_create(array('http' => array('method' => 'POST', 'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL, 'content' => "email=$email",),));
            $resp = json_decode(file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/user/isregistered.php', false, $headers), true);

            // если пользователь не зарегистрирован, редиректим на страницу регистрации с заполненными полями
            if ($resp['message'] == 'false') {
                $request_params = ['user_id' => $userId, 'fields' => 'photo_max', 'v' => '5.92', 'access_token' => $token];
                $get_params = http_build_query($request_params);
                $res = json_decode(file_get_contents('https://api.vk.com/method/users.get?' . $get_params));
                $avatar = ($res->response[0]->photo_max);
                $name = ($res->response[0]->first_name);
                $surname = ($res->response[0]->last_name);

                header('Location: register.php?email=' . $email . '&name=' . $name . '&surname=' . $surname . '&avatar=' . $avatar);
            } else {
                // иначе авторизируем
                function setAvatar($link)
                {
                    return (iconv_strlen($link) < 4) ? 'assets/img/defaultAvatar.png' : $link;
                }

                session_start();
                $result = json_decode(file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/user/get.php?email=' . $email), true);
                $_SESSION['name'] = $result['name'];
                $_SESSION['surname'] = $result['surname'];
                $_SESSION['email'] = $result['email'];
                $_SESSION['usergroup'] = $result['usergroup'];
                $_SESSION['avatar'] = setAvatar($result['avatar']);
                header('Location:/');
            }
        }
    }
} else if (isset($_GET['error'])) {

    echo('При авторизации произошла ошибка: ' . $_GET['error']
        . '. Error reason: ' . $_GET['error_reason']
        . '. Error description: ' . $_GET['error_description']);
    echo '<br><br><b>Возвращаем вас обратно...</b><script>setTimeout(function() {window.history.go(-2);},2000)</script>';
}
?>
