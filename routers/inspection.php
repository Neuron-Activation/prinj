<?php

$link = mysqli_connect("127.0.0.1", "backend", "password", "backend");

if (mysqli_connect_errno()) {
    http_response_code(500);
    echo "Failed to connect to database: " . mysqli_connect_error();
    return;
}

include_once 'utils/token.php';


function createPatient($name, $dateOfBirth, $gender) {
    global $link;

    if (!$link->query("INSERT INTO patients(name, date_of_birth, gender, create_time) VALUES('$name', '$dateOfBirth', '$gender', CURRENT_TIMESTAMP)")) {
        http_response_code(500);
        echo "Internal Server Error";
        return;
    } else {
        echo "Patient Succesfully created.";
    }
}


function getPatientCard($patientId) {
    global $link;

    $patientId = $link->real_escape_string($patientId);
    $result = $link->query("SELECT * from patients where id = '$patientId'");

    if ($link->error) {
        http_response_code(500);
        echo "Internal Server Error";
        return;
    }

    $patient = $result->fetch_assoc();

    if (!$patient) {
        http_response_code(404);
        echo "Patient not found";
        return;
    }

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

    if (!$result) {
        http_response_code(500);
        echo "Internal Server Error";
        return;
    }

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


function getInspectionChain($baseInspectionId) {
    global $link;
    
    $sql = "SELECT * FROM inspections WHERE base_inspection_id = " . intval($baseInspectionId) . " ORDER BY date DESC LIMIT 1";
    $result = mysqli_query($link, $sql);
    $latestInspection = mysqli_fetch_assoc($result);

    $inspections = array($latestInspection);
    $previousInspectionId = $latestInspection['previous_inspection_id'];
    while ($previousInspectionId != null) {
        $sql = "SELECT * FROM inspections WHERE id = " . intval($previousInspectionId);
        $result = mysqli_query($link, $sql);
        if (!$result) {
            http_response_code(500);
            echo "Internal Server Error";
            return;
        }
        $previousInspection = mysqli_fetch_assoc($result);
        array_push($inspections, $previousInspection);
        $previousInspectionId = $previousInspection['previous_inspection_id'];
    }

    echo json_encode($inspections);
}


function route($method, $urlList, $requestData) {

    $userId = checkToken();

    if (is_null($userId)) {
        http_response_code(401);
        echo "Unauthorized: Access denied.";
        return;
    }

    if ($method !== "POST" && $method !== "GET") {
        http_response_code(405);
        echo "Method Not Allowed";
        return;
    }

    if ($method === 'POST') {
        createPatient($requestData->body->name, $requestData->body->date_of_birth, $requestData->body->gender);
        return;
    }

    if ($method === 'GET' && count($urlList) === 2) {
        getPatientCard($urlList[1]);
        return;
    }

    if ($method === 'GET' && count($urlList) === 3 && $urlList[2] === "chain") {
        getInspectionChain($urlList[1]);
        return;
    } else {
        http_response_code(401);
        echo "Page Not Found";
        return;
    }
}

?>