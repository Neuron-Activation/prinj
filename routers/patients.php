<?php

include_once 'utils/token.php';


function route($method, $urlList, $requestData) {
    $link = mysqli_connect("127.0.0.1", "backend", "password", "backend");

    $userId = checkToken();

    $page = $_GET['page'] ?? 1;
    $pageSize = $_GET['pageSize'] ?? 5;
    $sorting = $_GET['sorting'] ?? 'NameAsc';
    $onlyMyPatients = isset($_GET['onlyMyPatients']) ? $_GET['onlyMyPatients'] : false;
    $query = isset($_GET['query']) ? $_GET['query'] : null;

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

    $sql = "SELECT * FROM patients";

    if ($query) {
        $sql .= " WHERE name LIKE '%" . mysqli_real_escape_string($link, $query) . "%'";
    }

    if ($onlyMyPatients) {
        $userId = checkToken();
        $sql .= ($query ? " AND" : " WHERE") . " EXISTS (SELECT 1 FROM inspections WHERE patient_id = patients.id AND doctor_id = " . intval($userId) . ")";
    }
    
    $sql .= " ORDER BY $orderBy LIMIT " . (($page - 1) * $pageSize) . ", " . $pageSize;
    
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