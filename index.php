<?php

header('Content-type: application/json');
$link = mysqli_connect("127.0.0.1", "backend", "password", "backend");

if (!$link) {
    echo "Ошибка: невозможно установить соединение с MySQL." . PHP_EOL;
    echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
    exit;
}

?>