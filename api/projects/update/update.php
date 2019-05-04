<?php
function update()
{
    $db = new Database();
    $tags = prepareTags($_REQUEST['tags']);
    $updateQuery = $db->connection->prepare("UPDATE projects_new SET title=:title, description=:description, members=:members, tags=:tags, curator=:curator, avatar=:avatar, deadline=:deadline, finish_date=:finish_date WHERE id=:id");
    $updateQuery->bindParam(':title', trim($_REQUEST['title']));
    $updateQuery->bindParam(':description', trim($_REQUEST['description']));
    $updateQuery->bindParam(':members', $_REQUEST['members']);
    $updateQuery->bindParam(':tags', $tags);
    $updateQuery->bindParam(':curator', $_REQUEST['curator']);
    $updateQuery->bindParam(':avatar', $_REQUEST['avatar']);
    $updateQuery->bindParam(':deadline', $_REQUEST['deadline']);
    $updateQuery->bindParam(':finish_date', $_REQUEST['finish_date']);
    $updateQuery->bindParam(':id', $_REQUEST['id']);
    $result = $updateQuery->execute();
    if ($result) {
        echo json_encode(['message' => 'true']);
    } else {
        echo json_encode(['message' => $updateQuery->errorInfo()[2]]);
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
