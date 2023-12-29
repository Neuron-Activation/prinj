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


function getPatientInspections($patientId, $page, $pageSize, $sorting, $icdRoots, $grouped) {
    global $link;

    switch ($sorting) {
        case 'DateAsc':
            $orderBy = "date ASC";
            break;
        case 'DateDesc':
            $orderBy = "date DESC";
            break;
        default:
            $orderBy = "date ASC";
    }

    $sql = "SELECT inspections.* FROM inspections WHERE patient_id = " . intval($patientId);

    if ($grouped) {
        $sql .= " AND previous_inspection_id IS NOT NULL";
    }

    $sql .= " ORDER BY $orderBy";

    $result = mysqli_query($link, $sql);
    $inspections = mysqli_fetch_all($result, MYSQLI_ASSOC);

    foreach ($inspections as $key => &$inspection) {
        $diagnosisSql = "SELECT diagnoses.* FROM inspections_diagnoses LEFT JOIN diagnoses ON inspections_diagnoses.diagnosis_code = diagnoses.diagnosis_code WHERE inspection_id = " . $inspection['id'];

        $diagnosisResult = mysqli_query($link, $diagnosisSql);
        $inspection['diagnoses'] = mysqli_fetch_all($diagnosisResult, MYSQLI_ASSOC);

        if ($icdRoots) {
            $icdRootsArray = explode(',', $icdRoots);
            $diagnosisCodes = array_column($inspection['diagnoses'], 'diagnosis_code');

            if (array_diff($icdRootsArray, $diagnosisCodes) || array_diff($diagnosisCodes, $icdRootsArray)) {
                unset($inspections[$key]);
                continue;
            }
        }
    }

    $inspections = array_slice($inspections, ($page - 1) * $pageSize, $pageSize);

    $response = [
        'inspections' => array_values($inspections),
        'pagination' => [
            'size' => $pageSize,
            'count' => count($inspections),
            'current' => $page
        ]
    ];

    echo json_encode($response);
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

    if ($method === 'GET' && count($urlList) === 3) {
        $page = $_GET['page'] ?? 1;
        $pageSize = $_GET['pageSize'] ?? 5;
        $sorting = $_GET['sorting'] ?? 'DateDesc';
        $icdRoots = isset($_GET['icdRoots']) ? $_GET['icdRoots'] : null;
        $grouped = isset($_GET['grouped']) ? $_GET['grouped'] : false;

        getPatientInspections($urlList[1], $page, $pageSize, $sorting, $icdRoots, $grouped);
        return;
    }
}

?>