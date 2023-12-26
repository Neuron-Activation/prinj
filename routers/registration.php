<?php

include_once 'validators/emailValidator.php';
include_once 'validators/phoneValidator.php';
include_once 'validators/genderValidator.php';


function route($method, $urlList, $requestData) {
    if ($method !== "POST") {
        http_response_code(405);
        echo "Method Not Allowed";
        return;
    }

    if (count($urlList) > 1) {
        http_response_code(404);
        echo "Page Not Found";
        return;
    }

    $link = mysqli_connect("127.0.0.1", "backend", "password", "backend");

    $email = $requestData->body->email;

    if (!validateEmail($email)) {
        http_response_code(400);
        echo "Invalid email format.";
        return;
    }

    $user = $link->query("SELECT id from users where email = '$email'")->fetch_assoc();

    if (!is_null($user)) {
        http_response_code(409);
        echo "User with this email already exists.";
        return;
    }

    $name = $requestData->body->name;
    $dateOfBirth = $requestData->body->date_of_birth;
    $gender = $requestData->body->gender;
    $phone = $requestData->body->phone;
    $email = $requestData->body->email;
    $password = hash("sha1", $requestData->body->password);
    $speciality = $requestData->body->speciality;

    if (!validatePhone($phone)) {
        http_response_code(400);
        echo "Invalid phone number format. Use +7 (xxx) xxx-xx-xx.";
        return;
    }

    if (!validateGender($gender)) {
        http_response_code(400);
        echo "Gender can only be male or female.";
        return;
    }

    if (!$link->query("INSERT INTO users(name, date_of_birth, gender, phone, email, speciality, password) VALUES('$name', '$dateOfBirth', '$gender', '$phone', '$email', '$speciality', '$password')")) {
        http_response_code(500);
        echo "Internal Server Error";
        return;
    }

    echo json_encode($requestData);
}

?>