<?php

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

    private function onSubmitForm()
    {
        if (isset($_POST['title']) && isset($_POST['description']) && isset($_POST['deadline']) && isset($_POST['tags']) && isset($_POST['roles']) && $_POST['teamsCount'] > 0 && $_POST['teamsCount'] < 6) {
            $title = trim(htmlspecialchars($_POST['title']));
            $desc = trim(htmlspecialchars($_POST['description']));
            $roles = array_slice(explode(',', $_POST['roles'], 11), 0, 10); // makes and array of roles and saves first 10 roles
            $members = $this->prepareMembers($roles, (int)$_POST['teamsCount']);
            $params = [
                'title' => $title, 'description' => $desc, 'deadline' => $_POST['deadline'], 'tags' => implode(',', $_POST['tags']), 'members' => json_encode($members)
            ];
            $this->addProject($params);
        } else echo 'Пожалуйста, заполните все поля.';
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

    // projects created by this user and which have status 0

    private function showDefaultContent()
    {
        echo '<a href="?createproject">Создать проект</a>';
        $this->showUnmoderredProjects();
        /*$this->showVerifiedProjects();*/
    }

    /*private function showVerifiedProject()
    {
        echo '<h2 style="text-align: center">Мои проекты</h2>';
        $newProjects = json_decode(@file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/projects/get.php?status=0&curator=' . $_SESSION['email']), true);
    }*/

    private function showUnmoderredProjects()
    {
        echo '<h2 style="text-align: center">Мои заявки на создание проектов</h2>';
        $newProjects = json_decode(@file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/projects/get.php?status=0&curator=' . $_SESSION['email']), true);
        $declinedProjects = json_decode(@file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/projects/get.php?status=3&curator=' . $_SESSION['email']), true);
        if (isset($newProjects['message']) && !isset($declinedProjects['message'])) {
            foreach ($declinedProjects as $project)
                $this->showProject($project);
        } else if (!isset($newProjects['message']) && isset($declinedProjects['message'])) {
            foreach ($newProjects as $project)
                $this->showProject($project);
        } else if (isset($newProjects['message']) && isset($declinedProjects['message'])) {
            echo '<b style="text-align: center">Нет проектов на рассмотрении администрации</b>';
        } else {
            $allProjects = array_merge($declinedProjects, $newProjects);
            foreach ($allProjects as $project)
                $this->showProject($project);
        }
    }
}