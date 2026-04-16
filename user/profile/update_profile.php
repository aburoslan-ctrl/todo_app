<?php
$method="POST";
$cache="no-cache";
include "../../head.php";

$tokenUser = ValidateAPITokenSentIN();
$user_id = $tokenUser->usertoken;

if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized();
    exit;
}

if (isset($_POST['fname'], $_POST['lname'], $_POST['password'])) {

    $fname    = cleanme($_POST['fname']);
    $lname    = cleanme($_POST['lname']);
    $password = cleanme($_POST['password']);

    if (
        input_is_invalid($fname) ||
        input_is_invalid($lname) ||
        input_is_invalid($password)
    ) {
        respondBadRequest("All fields are required.");
        exit;
    }

    else if (strlen($fname) < 3) {
        respondBadRequest("First name must be at least 3 characters.");
        exit;
    }

    else if (strlen($lname) < 3) {
        respondBadRequest("Last name must be at least 3 characters.");
        exit;
    }

    else if (strlen($password) < 6) {
        respondBadRequest("New password must be at least 6 characters.");
        exit;
    }

    $checkUser = $connect->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $checkUser->bind_param("i", $user_id);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows == 0) {
        respondBadRequest("User not found.");
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $updateProfile = $connect->prepare("
        UPDATE users
        SET fname = ?, lname = ?, password = ?
        WHERE user_id = ?
    ");

    $updateProfile->bind_param("sssi", $fname, $lname, $hashedPassword, $user_id);
    $updateProfile->execute();

    if ($updateProfile->affected_rows > 0) {
        $accesstoken = getTokenToSendAPI($user_id);
        respondOK(["access_token" => $accesstoken], "Profile updated successfully");
    } else {
        respondBadRequest("No changes made or update failed.");
    }

} else {
    respondBadRequest("Invalid request. All fields are required.");
}
?>
