<?php
$clientId = '6716519';
$clientSecret = 'CPK9OYPQAceSCyhluAbG';
$redirectUri = 'http://new.std-247.ist.mospolytech.ru/vk_auth.php';
$auth = new VkAuth($clientId, $clientSecret, $redirectUri);

final class VkAuth
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $data; // contains access_token, email, user_id

    /**
     * VkAuth constructor.
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUri
     */
    function __construct(string $clientId, string $clientSecret, string $redirectUri)
    {
        if (isset($_GET['code'])) { // if we've got a code from VK
            $this->clientId = $clientId;
            $this->clientSecret = $clientSecret;
            $this->redirectUri = $redirectUri;
            $this->data = $this->getTokenByCode();
            if ($this->data['email']) {
                if ($this->isRegistered($this->data['email']) == false) $this->redirectToRegister();
                else $this->loginUser();
            }
        } else if (isset($_GET['error'])) { // if VK has thrown an error, show it and redirect back
            echo('При авторизации произошла ошибка: ' . $_GET['error']
                . '. Причина: ' . $_GET['error_reason'] . '. Описание: ' . $_GET['error_description']);
            echo '<br><br><b>Возвращаем вас обратно...</b><script>setTimeout(function() {window.history.go(-2)},2500)</script>';
        }
    }

    /**
     * @return array|bool
     */
    private function getTokenByCode()
    {
        $content = @file_get_contents('https://oauth.vk.com/access_token?client_id=' . $this->clientId . '&client_secret=' . $this->clientSecret . '&redirect_uri=' . $this->redirectUri . '&code=' . $_GET['code']);
        if (!$content) {
            echo 'Ключ авторизации устарел. Повторите попытку авторизации.';
            return false;
        } else {
            $response = json_decode($content);
            if (isset($response->error)) {
                echo('При получении токена произошла ошибка. Попробуйте авторизоваться ещё раз. ');
                return false;
            } else return ['access_token' => $response->access_token, 'email' => $response->email, 'user_id' => $response->user_id];
        }
    }

    /**
     * @param $email
     * @return bool
     */
    private function isRegistered($email)
    {
        $headers = stream_context_create(array('http' => array('method' => 'POST', 'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL, 'content' => "email=$email"),));
        $resp = json_decode(file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/user/isregistered.php', false, $headers), true);
        if ($resp['message'] == 'false') return false;
        else return true;
    }

    private function loginUser()
    {
        session_start();
        $result = json_decode(file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/user/get.php?api_key=android&email=' . $this->data['email']), true);
        $_SESSION['name'] = $result['name'];
        $_SESSION['surname'] = $result['surname'];
        $_SESSION['email'] = $result['email'];
        $_SESSION['usergroup'] = $result['usergroup'];
        $_SESSION['avatar'] = $this->setAvatar($result['avatar']);
        header('Location:/');
    }

    private function redirectToRegister()
    {
        $request_params = ['user_id' => $this->data['user_id'], 'fields' => 'photo_max,contacts', 'v' => '5.92', 'access_token' => $this->data['access_token']];
        $get_params = http_build_query($request_params);
        $res = json_decode(file_get_contents('https://api.vk.com/method/users.get?' . $get_params));
        $avatar = ($res->response[0]->photo_max);
        $name = ($res->response[0]->first_name);
        $surname = ($res->response[0]->last_name);
        $phone = urlencode($res->response[0]->mobile_phone);
        header('Location: register.php?email=' . $this->data['email'] . '&name=' . $name . '&surname=' . $surname . '&avatar=' . $avatar . '&phone=' . $phone);
    }

    /**
     * @param $link
     * @return string
     */
    private function setAvatar($link)
    {
        return (iconv_strlen($link) < 4) ? 'assets/img/defaultAvatar.png' : $link;
    }
}
?>
