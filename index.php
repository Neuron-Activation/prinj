<?php

function getMethod() {
    return $_SERVER['REQUEST_METHOD'];
}


function getData($method) {
    $data = new stdClass();
    $data->body = json_decode(file_get_contents('php://input'));

    $data->parameters = [];
    $dataGet = $_GET;
    foreach ($dataGet as $key => $value) {
        if ($key != "q") {
            $data->parameters[$key] = $value;
        }
    }
    return $data;
}


header('Content-type: application/json');
$link = mysqli_connect("127.0.0.1", "backend", "password", "backend");

if (!$link) {
    echo "Ошибка: невозможно установить соединение с MySQL." . PHP_EOL;
    echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
    exit;
}

?>