<?php

include_once 'validators/emailValidator.php';
include_once 'validators/phoneValidator.php';
include_once 'validators/genderValidator.php';

include_once 'utils/token.php';


function route($method, $urlList, $requestData) {
    $link = mysqli_connect("127.0.0.1", "backend", "password", "backend");

    if (mysqli_connect_errno()) {
        http_response_code(500);
        echo "Failed to connect to database: " . mysqli_connect_error();
        return;
    }

    $userId = checkToken();

    if (is_null($userId)) {
        http_response_code(401);
        echo "Unauthorized: Access denied.";
        return;
    }

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

            if (empty($name) || empty($dateOfBirth) || empty($gender) || empty($phone) || empty($email)) {
                http_response_code(400);
                echo "All fields are required.";
                return;
            }

            if (!validateEmail($email)) {
                http_response_code(400);
                echo "Invalid email format.";
                return;
            }

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

            $userUpdateResult = $link->query("UPDATE users SET name = '$name', date_of_birth = '$dateOfBirth', gender = '$gender', phone = '$phone', email = '$email' WHERE id = '$userId'");

            if (!$userUpdateResult) {
                http_response_code(500);
                echo "Internal Server Error";
                return;
            } else {
                echo "User information has been successfully updated";
            }

            break;
        default:
            break;
    }
}

?>