<?php

$link = mysqli_connect("127.0.0.1", "backend", "password", "backend");

if (mysqli_connect_errno()) {
    http_response_code(500);
    echo "Failed to connect to database: " . mysqli_connect_error();
    return;
}

include_once 'utils/token.php';


function createInspection($date, $anamnesis, $complaints, $treatment, $conclusion, $nextVisitDate, $deathDate, $baseInspectionId, $previousInspectionId, $patientId, $consultationId, $diagnoses, $doctorId) {
    global $link;
    $sql = "INSERT INTO inspections(date, anamnesis, complaints, treatment, conclusion, next_visit_date, death_date, base_inspection_id, previous_inspection_id, patient_id, consultation_id, doctor_id, create_time) VALUES('$date', '$anamnesis', '$complaints', '$treatment', '$conclusion', '$nextVisitDate', '$deathDate', '$baseInspectionId', '$previousInspectionId', '$patientId', '$consultationId', '$doctorId', CURRENT_TIMESTAMP)";

    if (!$link->query($sql)) {
        http_response_code(500);
        echo "Internal Server Error";
        return;
    }


    $inspection_id = $link->insert_id;

    if (is_string($diagnoses)) {
        $diagnoses = array($diagnoses);
    }

    foreach ($diagnoses as $code) {
        $sql = "INSERT INTO inspections_diagnoses(inspection_id, diagnosis_code) VALUES('$inspection_id', '$code')";

        if (!$link->query($sql)) {
            http_response_code(500);
            echo "Internal Server Error";
            return;
        }
    }

    echo "Inspection created successfully.";
}


function getInspection($inspectionId) {
    global $link;

    $result = $link->query("SELECT * from inspections where id = '$inspectionId'");

    if (!$result) {
        http_response_code(500);
        echo "Internal Server Error";
        return;
    }

    $inspection = $result->fetch_assoc();

    if (!$inspection) {
        http_response_code(404);
        echo "Inspection not found";
        return;
    }

    echo json_encode($inspection);
}


function updateInspection($anamnesis, $complaints, $treatment, $conclusion, $nextVisitDate, $deathDate, $diagnoses, $inspectionId) {
    global $link;

    $sql = "UPDATE inspections SET anamnesis = '$anamnesis', complaints = '$complaints', treatment = '$treatment', conclusion = '$conclusion', next_visit_date = '$nextVisitDate', death_date = '$deathDate' WHERE id = '$inspectionId'";
    if (!$link->query($sql)) {
        http_response_code(500);
        echo "Internal Server Error";
        return;
    }

    $sql = "DELETE FROM inspections_diagnoses WHERE inspection_id = '$inspectionId'";
    if (!$link->query($sql)) {
        http_response_code(500);
        echo "Internal Server Error";
        return;
    }

    if (is_string($diagnoses)) {
        $diagnoses = array($diagnoses);
    }

    foreach ($diagnoses as $code) {
        $sql = "INSERT INTO inspections_diagnoses(inspection_id, diagnosis_code) VALUES('$inspectionId', '$code')";
        if (!$link->query($sql)) {
            http_response_code(500);
            echo "Internal Server Error";
            return;
        }
    }

    echo "Inspection updated successfully.";
}


function getInspectionChain($baseInspectionId) {
    global $link;

    $sql = "SELECT * FROM inspections WHERE base_inspection_id = " . intval($baseInspectionId) . " ORDER BY date DESC LIMIT 1";
    $result = mysqli_query($link, $sql);

    if (!$result) {
        http_response_code(500);
        echo "Internal Server Error";
        return;
    }

    $latestInspection = mysqli_fetch_assoc($result);

    if (!$latestInspection) {
        http_response_code(404);
        echo "Root inspection not found";
        return;
    }

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

    if ($method !== "POST" && $method !== "GET" && $method !== "PUT") {
        http_response_code(405);
        echo "Method Not Allowed";
        return;
    }

    if ($method === 'POST' && count($urlList) === 2 && $urlList[1] === "create") {
        $date = $requestData->body->date;
        $anamnesis = $requestData->body->anamnesis;
        $complaints = $requestData->body->complaints;
        $treatment = $requestData->body->treatment;
        $conclusion = $requestData->body->conclusion;
        $nextVisitDate = $requestData->body->nextVisitDate ?? null;
        $deathDate = $requestData->body->deathDate ?? null;
        $baseInspectionId = $requestData->body->baseInspectionId ?? null;
        $previousInspectionId = $requestData->body->previousInspectionId ?? null;
        $patientId = $requestData->body->patientId;
        $consultationId = $requestData->body->consultationId;
        $diagnoses = $requestData->body->diagnoses;

        createInspection($date, $anamnesis, $complaints, $treatment, $conclusion, $nextVisitDate, $deathDate, $baseInspectionId, $previousInspectionId, $patientId, $consultationId, $diagnoses, $userId);
        return;
    }

    if ($method === 'GET' && count($urlList) === 2) {
        getInspection($urlList[1]);
        return;
    }

    if ($method === 'PUT' && count($urlList) === 2) {
        $anamnesis = $requestData->body->anamnesis;
        $complaints = $requestData->body->complaints;
        $treatment = $requestData->body->treatment;
        $conclusion = $requestData->body->conclusion;
        $nextVisitDate = $requestData->body->nextVisitDate ?? null;
        $deathDate = $requestData->body->deathDate ?? null;
        $diagnoses = $requestData->body->diagnoses;

        updateInspection($anamnesis, $complaints, $treatment, $conclusion, $nextVisitDate, $deathDate, $diagnoses, $urlList[1]);
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