<?php session_start();
if (!isset($_SESSION['email'])) header('Location: /login.php'); // если отсутствует авторизация, переадресуем на авторизацию
$userCabinet = defineUser(); // определяет группу пользователя
function defineUser()
{
    switch ($_SESSION['usergroup']) {
        case 1:
            {
                return new WorkerCabinet;
                break;
            }
        case 2:
            {
                return new CuratorCabinet();
                break;
            }
        case 3:
            {
                return new AdminCabinet();
                break;
            }
        default:
            {
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
            margin:1% auto;
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
        // плюс стили, иконкаx
    }

    function showProject($project)
    {
        $description = substr($project->description, 0, 250) . '...'; // short description - first 250 symbols of full description
        $members = json_decode($project->members, true);
        $tags = $this->showProjectTags(json_decode($project->tags));
        $curator = json_decode(@file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/user/get.php?api_key=android&id=' . $project->curator));
        echo '<div class="projectWrapper"><a href="/project?id=' . $project->id . '" class="projectSnippet">';
        echo '<strong>' . $project->title . '</strong>';
        echo '<p>' . $description . '</p>';
        echo '<b>Занято: </b>' . $this->countPlaces($members) . '<br>';
        echo '<b>Куратор:</b> ' . $curator->name . ' ' . $curator->surname;
        echo $tags;
        echo '</a>';
        // administrative block that will not be shown for other users while displaying a snippet
        echo '<form class="commentForm" style="margin:1% 0 0 0;background:#eee;position:relative;z-index:2;" action="" method="post">
                <input type="hidden" name="projectId" value="' . $project->id . '">
                <label>Одобрить проект</label>
                <input type="radio" name="status" value="1" onclick="toggleCommentForm(event)"><br>
                <label>Отказать в публикации</label>
                <input type="radio" name="status" value="3" onclick="toggleCommentForm(event)"><br>
                <div class="commentArea" style="display:none;">
                    <label>Причина отказа в публикации:</label><br>
                    <textarea name="adm_comment" placeholder="Не более 255 символов" minlength="2" maxlength="255"></textarea>
                </div><button class="commentFormSubmit" type="submit" name="submit">Подтвердить</button>
              </form></div>';
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

    function showProjectTags($tags)
    {
        $result = '<div style="display:flex;justify-content: space-around">';
        for ($i = 0; $i < count($tags); $i++) {
            $result .= '<div style="background:#eee;">' . $tags[$i] . '</div>';
        }
        return $result . '</div>';
    }
}

final class WorkerCabinet extends Cabinet
{
    function __construct()
    {
        parent::__construct();
    }
}

final class CuratorCabinet extends Cabinet
{
    function __construct()
    {
        parent::__construct();

        if (isset($_GET['createproject'])) {
            if (isset($_POST['submit'])) {
                $this->onSubmitForm();
            } else {
                $this->showProjectCreatingForm();
            }
        } else $this->showDefaultContent();
    }

    /**
     * Prepares teams
     * @param array $roles
     * @param int $teams
     * @return array
     */
    private function prepareMembers(array $roles, int $teams)
    {
        $members = [];
        for ($i = 0; $i < $teams; $i++) {
            $members[$i] = $this->generateTeamInstance($roles);
        }
        return $members;
    }

    /**
     * Prepares members of a single team
     * @param $roles
     * @return array
     */
    private function generateTeamInstance($roles)
    {
        $team = [];
        foreach ($roles as $role) {
            $team[trim(htmlspecialchars($role))] = 0;
        };
        return $team;
    }

    private function onSubmitForm()
    {
        if (isset($_POST['title']) && isset($_POST['description']) && isset($_POST['deadline']) && isset($_POST['tags']) && isset($_POST['roles']) && $_POST['teamsCount'] > 0 && $_POST['teamsCount'] < 6) {
            $title = trim(htmlspecialchars($_POST['title']));
            $desc = trim(htmlspecialchars($_POST['description']));
            $roles = array_slice(explode(',', $_POST['roles'], 11), 0, 10); // makes and array of roles and saves first 10 roles
            $members = $this->prepareMembers($roles, (int)$_POST['teamsCount']);
            $params = [
                'title' => $title, 'description' => $desc, 'deadline' => $_POST['deadline'], 'tags' => json_encode($_POST['tags']), 'members' => json_encode($members)
            ];
            $this->addProject($params);
        } else echo 'Пожалуйста, заполните все поля.';
    }

    private function showProjectCreatingForm()
    {
        $today = date('Y-m-d');
        $maxDeadline = date('Y-m-d', strtotime('+1 year'));
        echo '<h1>Создание нового проекта</h1>
                <form action="" method="post">
                    <input name="title" type="text" placeholder="Название вашего проекта" maxlength="255" autofocus required><br>
                    <textarea name="description" placeholder="Описание вашего проекта" required cols="50" rows="5" style="resize:none;"></textarea><br>
                    <label for="">Крайняя дата записи на проект: </label><input type="date" name="deadline" min="' . $today . '" max="' . $maxDeadline . '" required><br><br>  
                    <label for="">Какие специалисты вам нужны? Перечислите через запятую не более 10 специальностей</label><br>
                    <input type="text" name="roles" placeholder="Например: Frontend-разработчик, SMM-специалист)" style="width:350px;" required><br>
                    <input type="number" required name="teamsCount" placeholder="Количество команд" min="1" max="5" style="width:150px"><br>
                    <label for="">Выберите теги необходимых компетенций, чтобы исполнителям было проще найти проект:</label><br>
                    <select required name="tags[]" multiple style="min-height:150px">
                        <optgroup label="IT-компетенции">
                            <option value="Frontend">Frontend</option>
                            <option value="Backend">Backend</option>
                            <option value="Веб-дизайн">Веб-дизайн</option>
                            <option value="Android">Android</option>
                            <option value="IOS">IOS</option>
                        </optgroup>
                        <optgroup label="Продвижение">
                            <option value="Маркетинг">Маркетинг</option>
                            <option value="SMM">SMM</option>
                            <option value="Контекстная реклама">Контекстная реклама</option>
                        </optgroup>
                    </select><br>
                    <button type="submit" name="submit">Создать проект</button> 
                </form>
                ';
    }

    private function showDefaultContent()
    {
        echo '<a href="?createproject">Создать проект</a>';
    }

    private function addProject($params)
    {
        $result = json_decode(file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/user/get.php?api_key=android&email=' . $_SESSION['email']), true);
        $params['curator'] = $result['id'];
        $params['api_key'] = 'android';
        $q = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/api/projects/create.php');
        curl_setopt($q, CURLOPT_POSTFIELDS, $params);
        curl_setopt($q, CURLOPT_RETURNTRANSFER, true);
        $response = json_decode(curl_exec($q), true);
        curl_close($q);
        if ($response['message'] === 'true') {
            header('Location:/cabinet');
        } else echo 'Упс! Что-то пошло не так при отправке запроса!<br>Перенаправляем в личный кабинет...';
    }
}

final class AdminCabinet extends Cabinet
{
    function __construct()
    {
        parent::__construct();
        if (isset($_POST['submit'])) {
            if ($_POST['status'] == 0 && iconv_strlen($_POST['adm_comment']) < 1) {
                echo '<h3 style="text-align: center">Вы не указали причину отказа!</h3>';
            } else if ($_POST['status'] != 1 && $_POST['status'] != 3) {
                echo '<h3 style="text-align: center">Указан неверный код статуса проекта!</h3>';
            } else {
                $params = $_POST;
                $params['api_key'] = 'android';
                $q = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/api/projects/updateProjectStatus.php');
                curl_setopt($q, CURLOPT_POSTFIELDS, $params);
                curl_setopt($q, CURLOPT_RETURNTRANSFER, true);
                $response = json_decode(curl_exec($q), true);
                curl_close($q);
                if ($response['message'] == 'true' && $_POST['status'] == 1) {
                    echo '<h3 style="text-align: center;color:forestgreen">Проект успешно одобрен!</h3>';
                } else if ($response['message'] == 'true' && $_POST['status'] == 3) {
                    echo '<h3 style="text-align: center;color:forestgreen">Проект был отклонён.</h3>';
                } else if ($response['message'] === 'Such value is already set') {
                    echo '<h3 style="text-align: center;color:darkred">Статус проекта уже был изменён!</h3>';
                } else {
                    echo '<h3 style="text-align: center;color:darkred">Не удалось изменить статус проекта!</h3> ' . $response['message'];
                }
            }
        }
        $this->showNewProjects();
        $this->printScripts();
    }

    function showNewProjects()
    {
        echo '<h2 style="text-align: center">Проекты, ожидающие модерации</h2>';
        $projects = json_decode(@file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/projects/get.php?status=0'));
        foreach ($projects as $project) {
            $this->showProject($project);
        }
    }

    function printScripts()
    {
        echo '<script>
        function toggleCommentForm(event) {
            let parent = event.target.parentNode;
            if(event.target.value==3) event.target.nextSibling.nextSibling.nextSibling.style.display="block";  
            else parent.lastChild.previousSibling.previousSibling.style.display="none";
        }
        </script>';
    }
}