<?php

include_once 'utils/token.php';


function route($method, $urlList, $requestData) {
    $link = mysqli_connect("127.0.0.1", "backend", "password", "backend");

    $userId = checkToken();

    $name = $requestData->body->name;
    $dateOfBirth = $requestData->body->date_of_birth;
    $gender = $requestData->body->gender;

    if (!$link->query("INSERT INTO patients(name, date_of_birth, gender, create_time) VALUES('$name', '$dateOfBirth', '$gender', CURRENT_TIMESTAMP)")) {
        http_response_code(500);
        echo "Internal Server Error";
        return;
    }
}

?>