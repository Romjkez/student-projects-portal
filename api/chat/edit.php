<?php
function edit(int $messageId, string $message)
{
    if (iconv_strlen(trim($message)) > 0 && iconv_strlen((trim($message))) < 256) {
        $db = new Database();
        $editQuery = $db->connection->prepare("UPDATE chat SET message=? WHERE message_id=?");
        $editQuery->bindValue(1, trim($message));
        $editQuery->bindValue(2, $messageId);
        $result = $editQuery->execute();
        if ($result) {
            return ['message' => 'true'];
        } else {
            return ['message' => $editQuery->errorInfo()[2]];
        }
    } else {
        return ['message' => 'Длина сообщения должна быть от 1 до 255 символов включительно'];
    }
}
