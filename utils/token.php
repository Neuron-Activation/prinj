<?php

function generateToken($link, $user) {
    $token = bin2hex(random_bytes(64));
    $validUntil = date('Y-m-d H:i:s', strtotime('+1 hour'));  
    $userID = $user['id'];

    $sql = "INSERT INTO tokens (value, user_id, valid_until) VALUES ('" . mysqli_real_escape_string($link, $token) . "', " . intval($userID) . ", '" . $validUntil . "')";
    $tokenInsertResult = mysqli_query($link, $sql);

    return $tokenInsertResult;
}


function getUserIdByToken($token) {
    global $link;
    $sql = "SELECT user_id FROM tokens WHERE value = '" . mysqli_real_escape_string($link, $token) . "' AND valid_until > CURRENT_TIMESTAMP";
    $result = mysqli_query($link, $sql);
    $user = mysqli_fetch_assoc($result);

    return $user ? $user['user_id'] : null;
}


function checkToken() {
    $token = substr(getallheaders()['Authorization'], 7);
    $userId = getUserIdByToken($token);

    return $userId;
}


function logout() {
    global $link;
    $token = substr(getallheaders()['Authorization'], 7);
    $sql = "UPDATE tokens SET valid_until = DATE_SUB(NOW(), INTERVAL 1 HOUR) WHERE value = '" . mysqli_real_escape_string($link, $token) . "'";
    $result = mysqli_query($link, $sql);

    if (!$result) {
        http_response_code(500);
        echo "Internal Server Error";
    } else {
        echo "Successfully logged out";
    }
}

?>