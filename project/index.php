<?php session_start();
printHeader();
if (isset($_GET['id'])) {
    $project = json_decode(@file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/projects/get.php?id=' . $_GET['id']));
    if (isset($project->message)) {
        http_response_code(404);
        echo 'Ошибка: ' . $project->message;
    } else {
        $curator = json_decode(file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/user/get.php?api_key=android&id=' . $project->curator), true); // object with project curator
        $deadline = date('d.m.Y', strtotime($project->deadline));
        $membersStr = prepareMembers(json_decode($project->members, true));
        $tags = prepareTags($project->tags);

        echo 'Проект #' . $project->id . '<br>';
        echo 'Название: ' . $project->title . '<br>';
        echo 'Описание: ' . $project->description . '<br>';
        echo 'Запись до: ' . $deadline . '<br>';
        echo 'Статус: ' . printProjectStatus($project->status);
        echo 'Куратор: <a href="/user?id=' . $project->curator . '">' . $curator['name'] . ' ' . $curator['surname'] . '</a><br>';
        echo 'Теги: ' . $tags . '<br>';
        echo 'Заполненность: ' . countPlaces(json_decode($project->members)) . '<br>';
        echo 'Участники:<br>' . $membersStr;
    }
}
function printProjectStatus($status)
{
    if ($status == 0) return '<b style="color:#d18d00">на рассмотрении администрации</b> <br>';
    else if ($status == 1) return '<b style="color:#0c8050;">открыто</b><br>';
    else if ($status == 2) return '<b style="color:#ff4d54">закрыто</b><br>';
    else if ($status == 3) return '<b style="color:#676767">не прошёл модерацию</b><br>';
    return 'неизвестно';
}

function prepareTags(string $tagsStr)
{
    $tags = explode(',', $tagsStr);
    $str = '';
    foreach ($tags as $tag) {
        $str .= '<span style="border:1px solid black;background:#eee;">' . $tag . '</span>';
    }
    return $str;
}
function prepareMembers(array $members)
{
    // todo повысить производительность или сделать асинхронной через js
    $str = '';
    for ($i = 0; $i < count($members); $i++) {
        $str = $str . '<br><u>Команда №' . $i . '</u><br>';
        foreach ($members[$i] as $key => $value) {
            if ($value == 0) {
                $str .= $key . ': <b style="color:#0c8050;">свободно</b><br>';
            } else {
                $member = json_decode(@file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/user/get.php?api_key=android&id=' . $value), true);
                if (!isset($member['message'])) {
                    $str .= $key . ': ' . '<a href="/user?id=' . $value . '">' . $member['name'] . ' ' . $member['surname'] . '</a><br>';
                } else $str .= $key . ': <i>пользователь не найден</i><br>';
            }
        }
    }
    return $str;
}

function printHeader()
{
    echo '<head>
    <title>Просмотр проекта</title>
    <meta charset="UTF-8">
    </head><body>';
}

// returns occupied places/all places(ex.: 2/6)
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
