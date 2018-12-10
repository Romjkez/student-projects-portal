<?php
$clientId = '9720b31b284b4caf812835ac8e672eb3';
$client_secret = 'dacf68b1f5ee4b6096ec57d9dfa7bd1d';
$redirectUri = 'http://new.std-247.ist.mospolytech.ru/oauth/yandex_auth.php';
$auth = new YandexAuth($clientId, $client_secret, $redirectUri);

final class YandexAuth
{
    private $client_secret;
    private $redirectUri;
    private $clientId;
    private $access_token;
    private $data; // contains first_name, last_name, is_avatar_empty(true/false), default_avatar_id, default_email etc

    function __construct(string $clientId, $client_secret, $redirectUri)
    {
        if (isset($_GET['code'])) {
            $this->redirectUri = $redirectUri;
            $this->client_secret = $client_secret;
            $this->clientId = $clientId;
            $this->access_token = $this->getTokenByCode();
            if ($this->access_token !== false) {
                $this->data = $this->getUserInfo();
                if ($this->isRegistered($this->data['default_email']) == true) {
                    $this->loginUser();
                } else $this->redirectToRegister();
            }
        } else if (isset($_GET['error'])) {
            echo 'Ошибка: ' . $_GET['error'] . ': ' . $_GET['error_description'];
            echo '<br><br><b>Возвращаем вас обратно...</b><script>setTimeout(function() {window.history.go(-2)},2500)</script>';
        }
    }

    private function getTokenByCode()
    {
        $params = http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $_GET['code'],
            'client_id' => $this->clientId,
            'client_secret' => $this->client_secret
        ]);

        $q = curl_init('https://oauth.yandex.ru');
        curl_setopt($q, CURLOPT_URL, 'https://oauth.yandex.ru/token');
        curl_setopt($q, CURLOPT_POST, 1);
        curl_setopt($q, CURLOPT_POSTFIELDS, $params);
        curl_setopt($q, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($q);
        curl_close($q);

        $response = json_decode($content);
        if (isset($response->error)) {
            echo ('Ошибка авторизации: ') . $response->error_description;
            echo '<br><br><b>Возвращаем вас обратно...</b><script>setTimeout(function() {window.history.go(-2)},2500)</script>';
            return false;
        } else return $response->access_token;

    }

    private function getUserInfo()
    {
        $headers = stream_context_create(array('http' => array('method' => 'GET', 'header' => 'Authorization: OAuth ' . $this->access_token . PHP_EOL),));
        return json_decode(file_get_contents('https://login.yandex.ru/info?format=json&with_openid_identity=0', false, $headers), true);
    }

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
        $result = json_decode(file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/user/get.php?api_key=android&email=' . $this->data['default_email']), true);
        $_SESSION['name'] = $result['name'];
        $_SESSION['surname'] = $result['surname'];
        $_SESSION['email'] = $result['email'];
        $_SESSION['usergroup'] = $result['usergroup'];
        $_SESSION['avatar'] = $this->setAvatar($result['avatar']);
        header('Location:/');
    }

    private function setAvatar($link)
    {
        return (iconv_strlen($link) < 4) ? 'assets/img/defaultAvatar.png' : $link;
    }

    private function redirectToRegister()
    {
        if ($this->data['is_avatar_empty'] == true) {
            $avatar = '';
        } else {
            $avatar = 'https://avatars.yandex.net/get-yapic/' . $this->data['default_avatar_id'] . '/islands-200';
        }
        header('Location: /register.php?email=' . $this->data['default_email'] . '&name=' . $this->data['first_name'] . '&surname=' . $this->data['last_name'] . '&avatar=' . $avatar);
    }
}