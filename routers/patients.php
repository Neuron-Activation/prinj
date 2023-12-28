<?php

function route($method, $urlList, $requestData) {
    $link = mysqli_connect("127.0.0.1", "backend", "password", "backend");

    $page = $_GET['page'] ?? 1;
    $pageSize = $_GET['pageSize'] ?? 5;
    $sorting = $_GET['sorting'] ?? 'NameAsc';

    switch ($sorting) {
        case 'NameAsc':
            $orderBy = "name ASC";
            break;
        case 'NameDesc':
            $orderBy = "name DESC";
            break;
        case 'CreateAsc':
            $orderBy = "create_time ASC";
            break;
        case 'CreateDesc':
            $orderBy = "create_time DESC";
            break;
        default:
            $orderBy = "name ASC";
    }

    $sql = "SELECT * FROM patients ORDER BY $orderBy LIMIT " . (($page - 1) * $pageSize) . ", " . $pageSize;

    $result = mysqli_query($link, $sql);
    $patients = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $response = [
        'patients' => $patients,
        'pagination' => [
            'size' => $pageSize,
            'count' => count($patients),
            'current' => $page
        ]
    ];

    echo json_encode($response);
}

?>