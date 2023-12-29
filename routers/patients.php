<?php

include_once 'utils/token.php';


function route($method, $urlList, $requestData) {
    $link = mysqli_connect("127.0.0.1", "backend", "password", "backend");

    if (mysqli_connect_errno()) {
        http_response_code(500);
        echo "Failed to connect to database: " . mysqli_connect_error();
        return;
    }

    $userId = checkToken();

    if (is_null($userId)) {
        http_response_code(401);
        echo "Unauthorized: Access denied.";
        return;
    }

    if ($method !== "GET") {
        http_response_code(405);
        echo "Method Not Allowed";
        return;
    }

    if (count($urlList) > 1) {
        http_response_code(404);
        echo "Page Not Found";
        return;
    }

    $page = $_GET['page'] ?? 1;
    $pageSize = $_GET['pageSize'] ?? 5;
    $sorting = $_GET['sorting'] ?? 'NameAsc';
    $onlyMyPatients = isset($_GET['onlyMyPatients']) ? $_GET['onlyMyPatients'] : false;
    $query = isset($_GET['query']) ? $_GET['query'] : null;
    $conclusion = isset($_GET['conclusion']) ? $_GET['conclusion'] : null;
    $scheduledVisits = isset($_GET['scheduledVisits']) ? $_GET['scheduledVisits'] : false;

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
        case 'InspectionAsc':
            $orderBy = "(SELECT MAX(date) FROM inspections WHERE patient_id = patients.id) ASC";
            break;
        case 'InspectionDesc':
            $orderBy = "(SELECT MAX(date) FROM inspections WHERE patient_id = patients.id) DESC";
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

    if ($conclusion) {
        $sql .= ($query || $onlyMyPatients ? " AND" : " WHERE") . " EXISTS (SELECT 1 FROM inspections WHERE patient_id = patients.id AND conclusion = '" . mysqli_real_escape_string($link, $conclusion) . "')";
    }

    if ($scheduledVisits) {
        $sql .= ($query || $onlyMyPatients || $conclusion ? " AND" : " WHERE") . " EXISTS (SELECT 1 FROM inspections WHERE patient_id = patients.id AND next_visit_date > NOW())";
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