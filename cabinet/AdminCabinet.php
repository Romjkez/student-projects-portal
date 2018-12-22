<?php

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
        $projects = json_decode(@file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/projects/get.php?status=0'), true);
        if ($projects[' message'] == 'No projects found') {
            echo '<div style="text-align: center;"><i>Все проекты промодерированы!</i></div>';
        } else
            foreach ($projects as $project) {
                $this->showProject($project);
            }
    }

    function showProject($project)
    {
        parent::showProject($project);
        // administrative block that will not be shown for other users while displaying a snippet
        echo '<form class="commentForm" style="margin:1% 0 0 0;background:#eee;position:relative;z-index:2;" action="" method="post">
                <input type="hidden" name="id" value="' . $project->id . '">
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