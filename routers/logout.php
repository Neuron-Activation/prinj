<?php

include_once 'utils/token.php';

$link = mysqli_connect("127.0.0.1", "backend", "password", "backend");


function route($method, $urlList, $requestData) {
    if ($method === 'POST' && count($urlList) === 1) {
        checkToken();
        logout();
    }
}

?>