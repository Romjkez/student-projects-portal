<?php
require_once '../headers.php';
echo processQuery();
function processQuery()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (iconv_strlen($_POST['email']) > 4 && iconv_strlen($_POST['surname']) > 1 && is_numeric($_POST['id'])) {
            require_once '../../database.php';
            $db = new Database();
            $data = prepareData($_POST);
            $checkEmail = $db->connection->prepare("SELECT id FROM `users` WHERE email=:email");
            $checkEmail->bindParam(':email', $data['email']);
            $checkEmail->execute();
            $emailResult = $checkEmail->fetch();
            $emails = $checkEmail->rowCount();
            if ($emailResult[0] != $data['id'] && $emails > 0) {
                http_response_code(200);
                return json_encode(['message' => 'This email is already registered by another user']);
            } else {
                if (!isset($data['name'])) $data['name'] = '';
                if (!isset($data['middlename'])) $data['middlename'] = '';
                if (!isset($data['tel'])) $data['tel'] = '';
                if (!isset($data['std_group'])) $data['std_group'] = '';
                if (!isset($data['description'])) $data['description'] = '';
                if (!isset($data['avatar'])) $data['avatar'] = '';

                if (isset($data['pass'])) {
                    if (isset($data['old_pass'])) {
                        $checkPass = $db->connection->prepare("SELECT password FROM users WHERE id=:id");
                        $checkPass->bindParam(':id', $data['id']);
                        $checkPass->execute();
                        $old_pass = $checkPass->fetch();
                        if (password_verify($data['old_pass'], $old_pass[0]) == true) {
                            $pass = password_hash($data['pass'], PASSWORD_DEFAULT);
                            $q = $db->connection->prepare(
                                "UPDATE `users` SET password=:password, name=:name, surname=:surname, middle_name=:middle_name, email=:email, phone=:phone, stdgroup=:stdgroup, description=:description,avatar=:avatar WHERE id=:id ");
                            $q->bindParam(':password', $pass);
                            $q->bindParam(':name', $data['name']);
                            $q->bindParam(':surname', $data['surname']);
                            $q->bindParam(':middle_name', $data['middlename']);
                            $q->bindParam(':email', $data['email']);
                            $q->bindParam(':phone', $data['tel']);
                            $q->bindParam(':stdgroup', $data['std_group']);
                            $q->bindParam(':description', $data['description']);
                            $q->bindParam(':avatar', $data['avatar']);
                            $q->bindParam(':id', $data['id']);
                        } else return json_encode(['message' => 'Current password is not correct']);
                    } else return json_encode(['message' => 'Specify both old password and new password']);
                } else {
                    $q = $db->connection->prepare(
                        "UPDATE `users` SET name=:name, surname=:surname, middle_name=:middle_name, email=:email, phone=:phone, stdgroup=:stdgroup, description=:description,avatar=:avatar WHERE id=:id");
                }
                $q->bindParam(':name', $data['name']);
                $q->bindParam(':surname', $data['surname']);
                $q->bindParam(':middle_name', $data['middlename']);
                $q->bindParam(':email', $data['email']);
                $q->bindParam(':phone', $data['tel']);
                $q->bindParam(':stdgroup', $data['std_group']);
                $q->bindParam(':description', $data['description']);
                $q->bindParam(':avatar', $data['avatar']);
                $q->bindParam(':id', $data['id']);
                $result = $q->execute();

                if ($data['active_projects'] > 0) {
                    $act = $db->connection->prepare("UPDATE users SET active_projects=:active WHERE id=:id");
                    $act->bindParam(':id', $data['id']);
                    $act->bindParam(':active', $data['active_projects']);
                    $result = $act->execute();
                }
                if ($data['finished_projects'] > 0) {
                    $act = $db->connection->prepare("UPDATE users SET finished_projects=:finished WHERE id=:id");
                    $act->bindParam(':id', $data['id']);
                    $act->bindParam(':active', $data['finished_projects']);
                    $result = $act->execute();
                }

                if ($result == true) {
                    http_response_code(202);
                    return json_encode(['message' => 'true']);
                } else {
                    http_response_code(200);
                    return json_encode(['message' => 'false']);
                }
            }
        } else {
            http_response_code(200);
            return json_encode(['message' => "Specify email(>4 symbols), id(numeric) and surname(>1 symbol)"]);
        }
    } else {
        http_response_code(405);
        return json_encode(['message' => 'Method not supported']);
    }
}

function prepareData($array)
{
    $data = [];
    foreach ($array as $key => $value) {
        if (iconv_strlen($value) > 0) {
            $data[$key] = htmlspecialchars(trim($value));
        }
    }
    return $data;
}
