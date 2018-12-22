<?php session_start();
if (!isset($_SESSION['email'])) header('Location: /login.php'); // если отсутствует авторизация, переадресуем на авторизацию
$userCabinet = defineUser(); // определяет группу пользователя
function defineUser()
{
    switch ($_SESSION['usergroup']) {
        case 1:
            {
                require_once 'WorkerCabinet.php';
                return new WorkerCabinet;
                break;
            }
        case 2:
            {
                require_once 'CuratorCabinet.php';
                return new CuratorCabinet();
                break;
            }
        case 3:
            {
                require_once 'AdminCabinet.php';
                return new AdminCabinet();
                break;
            }
        default:
            {
                require_once 'WorkerCabinet.php';
                return new WorkerCabinet;
            }
    }
}

class Cabinet
{
    function __construct()
    {
        $this->printHeader();
    }

    function printHeader()
    {
        echo '
        <head>
            <title>Личный кабинет</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <style>body{font-family: Arial,Helvetica,sans-serif}
            .projectWrapper{
            border:1px solid #000;
            width:50%;
            margin:1% auto 0 auto;
            }
            .projectSnippet{
            display: block;
            transition: background-color 200ms;
            text-decoration: none;
            color: #000;
            }
            .projectSnippet:hover{
            background-color:#eee;
           
            }
            </style>
        </head>';
        // плюс стили, иконка
    }

    /**
     * @param $project
     */
    function showProject(array $project)
    {
        $description = substr($project['description'], 0, 250) . '...'; // short description - first 250 symbols of full description
        $members = json_decode($project['members'], true);
        $tags = $this->showProjectTags($project['tags']);
        $curator = json_decode(@file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/user/get.php?api_key=android&id=' . $project['curator']));
        echo '<div class="projectWrapper"><a href="/project?id=' . $project['id'] . '" class="projectSnippet">';
        echo '<strong>' . $project['title'] . '</strong>';
        echo '<p>' . $description . '</p>';
        echo '<b>Статус:</b> ' . $this->printProjectStatus($project['status']);
        echo '<b>Занято: </b>' . $this->countPlaces($members) . '<br>';
        echo '<b>Куратор:</b> ' . $curator->name . ' ' . $curator->surname;
        echo $tags;
        // for curators
        if (!empty($project['adm_comment'])) {
            echo '<div style="background: #ff4d54;color:#fff">Отказ в публикации. Комментарий администратора: <b>' . $project['adm_comment'] . '</b></div>';
            echo '<div><form action="" method="post"><a style="border:1px solid black;background:#eee;text-decoration: none;color:#000" href="/project?id=' . $project['id'] . '&edit">Редактировать</a><button disabled>Отправить снова</button><button disabled>Удалить проект</button></form></div>';
        }
        echo '</a></div>';
    }

    function printProjectStatus($status)
    {
        if ($status == 0) return '<b style="color:#d18d00">На рассмотрении администрации</b> <br>';
        else if ($status == 1) return '<b style="color:#0c8050;">Открыто</b><br>';
        else if ($status == 2) return '<b style="color:#ff4d54">Закрыто</b><br>';
        else if ($status == 3) return '<b style="color:#676767">Не прошёл модерацию</b><br>';
        return 'неизвестно';
    }

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

    function showProjectTags(string $tagsStr)
    {
        $tags = explode(',', $tagsStr);
        $result = '<div style="display:flex;justify-content: space-around">';
        for ($i = 0; $i < count($tags); $i++) {
            $result .= '<div style="background:#eee;">' . $tags[$i] . '</div>';
        }
        return $result . '</div>';
    }
}