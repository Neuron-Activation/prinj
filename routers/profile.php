<?php

function route($method, $urlList, $requestData) {
    $link = mysqli_connect("127.0.0.1", "backend", "password", "backend");

    $userId = checkToken();

    switch ($method) {
        case 'GET':
            $user = $link->query("SELECT full_name, date_of_birth, gender, phone, email, speciality, id from users where id = '$userId'")->fetch_assoc();
            echo json_encode($user);
            break;
        case 'PUT':
            $user = $link->query("SELECT * from users where id = '$userId'")->fetch_assoc();

            $name = $requestData->body->name;
            $dateOfBirth = $requestData->body->date_of_birth;
            $gender = $requestData->body->gender;
            $phone = $requestData->body->phone;
            $email = $requestData->body->email;

            $link->query("UPDATE users SET name = '$name', date_of_birth = '$dateOfBirth', gender = '$gender', phone = '$phone', email = '$email' WHERE id = '$userId'");

            break;
        default:
            break;
    }
}

?>