<?php
if (isset($_GET['access_token'])) {

    // AUTH LINK: https://oauth.vk.com/authorize?client_id=6716519&display=page&redirect_uri=http://new.std-247.ist.mospolytech.ru/callback.php&scope=email,offline&response_type=token&v=5.92&state=1
    $email = $_GET['email'];
    $token = $_GET['access_token'];
    $user_id = $_GET['user_id'];

    $request_params = ['user_id' => $user_id, 'fields' => 'photo_max', 'v' => '5.92', 'access_token' => $token];

    $get_params = http_build_query($request_params);
    $result = json_decode(file_get_contents('https://api.vk.com/method/users.get?' . $get_params));
    $avatar = ($result->response[0]->photo_max);
    $name = ($result->response[0]->first_name);
    $surname = ($result->response[0]->last_name);

    $headers = stream_context_create(array('http' => array('method' => 'POST', 'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL, 'content' => "email=$email",),));
    $resp = json_decode(file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/user/isregistered.php', false, $headers), true);

    if ($resp['message'] == 'false') {
        header('Location: register.php?email=' . $email . '&name=' . $name . '&surname=' . $surname . '&avatar=' . $avatar);
    } else {
        session_start();
        $result = json_decode(file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/user/get.php?email=' . $email), true);
        $_SESSION['name'] = htmlspecialchars($result['name']);
        $_SESSION['surname'] = htmlspecialchars($result['surname']);
        $_SESSION['email'] = htmlspecialchars($result['email']);
        header('Location:/');
    }
    //TODO авторизацию защитить от фейкового access_token(фейковый токен позволит логиниться под любым email)
} else {
    echo "<script> let hash=location.hash.substring(1);
        if(hash.length>0)window.location.href='?'+hash</script>";
}

?>
