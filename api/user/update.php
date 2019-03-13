<?php
require_once '../headers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../database.php';
    $db = new Database();
    $data = prepareData($_POST);
    if (isset($data['password'])) {
        $pass = password_hash($data['pass'], PASSWORD_DEFAULT);
        $q = $db->connection->prepare(
            "UPDATE `users` SET password=:password, name=:name, surname=:surname, middle_name=:middlename, email=:email, phone=:phone, stdgroup=:std_group, description=:description,avatar=:avatar WHERE `users`.`id`=:id ");
        //todo забиндить параметры и расписать запрос без пароля
    } else {

    }
}
function prepareData($array)
{
    $data = [];
    foreach ($array as $key => $value) {
        if (iconv_strlen($value) > 0) {
            $data[$key] = $value;
        }
    }
    return $data;
}
