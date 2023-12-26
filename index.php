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

$url = isset($_GET['q']) ? $_GET['q'] : '';
$url = rtrim($url, '/');
$urlList = explode('/', $url);

$router = $urlList[0];
$requestData = getData(getMethod());
$method = getMethod();

if(file_exists(realpath(dirname(__FILE__)).'/routers/' . $router . '.php')) {
    include_once 'routers/' . $router . '.php';
    route($method, $urlList, $requestData);
}
else {
    http_response_code(404);
    echo "404 Not Found";
}

?>