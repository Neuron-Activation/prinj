<?php

include_once 'utils/token.php';
include_once 'validators/emailValidator.php';


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

    if (mysqli_connect_errno()) {
        http_response_code(500);
        echo "Failed to connect to database: " . mysqli_connect_error();
        return;
    }

    $email = $requestData->body->email;

    if (!validateEmail($email)) {
        http_response_code(400);
        echo "Invalid email format.";
        return;
    }

    $password = hash("sha1", $requestData->body->password);
    $user = $link->query("SELECT id from users where email = '$email' AND password = '$password'")->fetch_assoc();

    if (!is_null($user)) {
            $token = generateToken($link, $user);
            echo json_encode(array("token" => $token));
        } else {
            http_response_code(401);
            echo "Unauthorized: User with this email and password not found.";
        }
}

?>