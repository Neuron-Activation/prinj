<?php

function validatePhone($phone) {
    $phonePattern = "/^\\+7 \\([0-9]{3}\\) [0-9]{3}-[0-9]{2}-[0-9]{2}$/";
    return preg_match($phonePattern, $phone);
}

?>