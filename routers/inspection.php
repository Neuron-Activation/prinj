<?php

$link = mysqli_connect("127.0.0.1", "backend", "password", "backend");

include_once 'utils/token.php';


function createInspection($date, $anamnesis, $complaints, $treatment, $conclusion, $nextVisitDate, $deathDate, $baseInspectionId, $previousInspectionId, $patientId, $consultationId, $diagnoses, $doctorId) {
    global $link;
    $sql = "INSERT INTO inspections(date, anamnesis, complaints, treatment, conclusion, next_visit_date, death_date, base_inspection_id, previous_inspection_id, patient_id, consultation_id, doctor_id, create_time) VALUES('$date', '$anamnesis', '$complaints', '$treatment', '$conclusion', '$nextVisitDate', '$deathDate', '$baseInspectionId', '$previousInspectionId', '$patientId', '$consultationId', '$doctorId', CURRENT_TIMESTAMP)";
    $link->query($sql);
    
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
}


function getInspection($inspectionId) {
    global $link;

    $result = $link->query("SELECT * from inspections where id = '$inspectionId'");

    $inspection = $result->fetch_assoc();

    if (!$inspection) {
        http_response_code(404);
        echo "Patient not found";
        return;
    }

    echo json_encode($inspection);
}


function route($method, $urlList, $requestData) {

    $userId = checkToken();

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
}

?>