<?php
function route($method, $urlList, $requestData) {
    $link = mysqli_connect("127.0.0.1", "backend", "password", "backend");

    if ($method == "POST") {
        $email = $requestData->body->email;
        $user = $link->query("SELECT id from users where email = '$email'")->fetch_assoc();

        if (is_null($user)) {
            $name = $requestData->body->name;
            $dateOfBirth = $requestData->body->date_of_birth;
            $gender = $requestData->body->gender;
            $phone = $requestData->body->phone;
            $email = $requestData->body->email;
            $password = hash("sha1", $requestData->body->password);
            $speciality = $requestData->body->speciality;

            $link->query("INSERT INTO users(name, date_of_birth, gender, phone, email, speciality, password) VALUES('$name', '$dateOfBirth', '$gender', '$phone', '$email', '$speciality', '$password')");

            echo json_encode($requestData);
        }

    }
}

?>