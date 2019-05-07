<?php
function delete(int $fileId)
{
    if (is_numeric($fileId)) {
        $db = new Database();

        $fileQuery = $db->connection->prepare("SELECT link FROM files WHERE id=?");
        $fileQuery->bindValue(1, $fileId);
        $fileQuery->execute();
        if ($fileQuery->rowCount() > 0) {
            $fileLink = $fileQuery->fetch()[0];
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $fileLink)) {
                $res = unlink($_SERVER['DOCUMENT_ROOT'] . $fileLink);
                if ($res) {
                    $deleteQuery = $db->connection->prepare("DELETE FROM files WHERE id=?");
                    $deleteQuery->bindValue(1, $fileId);
                    $result = $deleteQuery->execute();
                    if ($result) {
                        http_response_code(200);
                        return ['message' => 'true'];
                    } else {
                        http_response_code(422);
                        return ['message' => $deleteQuery->errorInfo()[2]];
                    }
                } else {
                    http_response_code(422);
                    return ['message' => 'Удаляемый файл не найден на сервере'];
                }
            }
        } else {
            http_response_code(404);
            return ['message' => 'Файл с указанным ID не найден'];
        }
    } else {
        http_response_code(400);
        return ['message' => WRONG_OR_MISSING_PARAMS_ERROR];
    }
}
