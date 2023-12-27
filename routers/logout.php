<?php

include_once 'utils/token.php';

$link = mysqli_connect("127.0.0.1", "backend", "password", "backend");

if (mysqli_connect_errno()) {
    http_response_code(500);
    echo "Failed to connect to database: " . mysqli_connect_error();
    return;
}


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

    $userId = checkToken();

        if (!$userId) {
            http_response_code(401);
            echo "Unauthorized: Access denied.";
            return;
        }

        logout();
}

?>