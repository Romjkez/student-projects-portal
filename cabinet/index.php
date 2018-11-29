<?php session_start();
if (!isset($_SESSION['email'])) header('Location: login.php'); // если отсутствует авторизация, переадресуем на авторизацию
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
        </head>';
        // плюс стили, иконка
    }
}

class WorkerCabinet extends Cabinet
{
    function __construct()
    {
        parent::__construct();
    }
}

class CuratorCabinet extends Cabinet
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

    private function onSubmitForm()
    {
        if (isset($_POST['title']) && isset($_POST['description']) && isset($_POST['deadline']) && isset($_POST['tags']) && isset($_POST['roles']) && $_POST['teamsCount'] > 0 && $_POST['teamsCount'] < 6) {
            echo strtotime($_POST['deadline']);
            // todo доделать валидацию и отправку формы на апи

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
                    <label for="">Какие специалисты вам нужны? Перечислите через запятую</label><br>
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

    private function addProject()
    {

    }
}

class AdminCabinet extends Cabinet
{
    function __construct()
    {
        parent::__construct();
    }
}