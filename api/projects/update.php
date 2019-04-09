<?php
require_once '../headers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (checkArguments()) {
        require_once '../../database.php';
        $db = new Database();
        $tags = prepareTags($_POST['tags']);
        $q = $db->connection->prepare("UPDATE projects_new SET title=:title, description=:description, members=:members, tags=:tags, curator=:curator, avatar=:avatar, deadline=:deadline, finish_date=:finish_date, files=:files WHERE id=:id");
        $q->bindParam(':title', trim($_POST['title']));
        $q->bindParam(':description', trim($_POST['description']));
        $q->bindParam(':members', $_POST['members']);
        $q->bindParam(':tags', $tags);
        $q->bindParam(':curator', $_POST['curator']);
        $q->bindParam(':avatar', $_POST['avatar']);
        $q->bindParam(':deadline', $_POST['deadline']);
        $q->bindParam(':finish_date', $_POST['finish_date']);
        $q->bindParam(':files', $_POST['files']);
        $q->bindParam(':id', $_POST['id']);
        $result = $q->execute();

        if ($result) {
            echo json_encode(['message' => 'true']);
        } else {
            echo json_encode(['message' => 'false']);
        }
    } else {
        echo json_encode(['message' => 'Specify all the necessary parameters. Title and description must be > 2 symbols length']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}

function checkArguments()
{
    if (iconv_strlen(trim($_POST['title'])) > 2 && iconv_strlen(trim($_POST['description'])) > 2 && isset($_POST['id']) && isset($_POST['members'])
        && is_numeric($_POST['curator']) && isset($_POST['avatar']) && isset($_POST['files']) && isset($_POST['deadline'])
        && isset($_POST['finish_date']) && isset($_POST['tags'])) {
        return true;
    } else {
        return false;
    }
}

function prepareTags($tags)
{
    $tags = explode(',', $tags, 8);
    $result = [];
    for ($i = 0; $i < count($tags); $i++) {
        if (count($tags[$i]) > 0) {
            $result[$i] = $tags[$i];
        }
    }
    return implode(',', $result);
}

