<?php

$link = mysqli_connect("127.0.0.1", "backend", "password", "backend");

include_once 'utils/token.php';


function createPatient($name, $dateOfBirth, $gender) {
    global $link;

    if (!$link->query("INSERT INTO patients(name, date_of_birth, gender, create_time) VALUES('$name', '$dateOfBirth', '$gender', CURRENT_TIMESTAMP)")) {
        http_response_code(500);
        echo "Internal Server Error";
        return;
    }
}

function getPatientCard($patientId) {
    global $link;

    $patient = $link->query("SELECT * from patients where id = '$patientId'")->fetch_assoc();
    echo json_encode($patient);
}



function route($method, $urlList, $requestData) {
   
    $userId = checkToken();

    if ($method === 'POST') {
        createPatient($requestData->body->name, $requestData->body->date_of_birth, $requestData->body->gender);
        return;
    }

    if ($method === 'GET' && count($urlList) === 2) {
        getPatientCard($urlList[1]);
        return;
    }
}

?>