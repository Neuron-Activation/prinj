<?php

include_once 'utils/token.php';
include_once 'validators/emailValidator.php';


function route($method, $urlList, $requestData) {
    if ($method == "POST") {
        $link = mysqli_connect("127.0.0.1", "backend", "password", "backend");
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
        }
    }
}

?>