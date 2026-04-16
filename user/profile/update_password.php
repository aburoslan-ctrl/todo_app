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

if (isset($_POST['old_password'], $_POST['new_password'])) {

    $old_password = cleanme($_POST['old_password']);
    $new_password = cleanme($_POST['new_password']);

    if (
        input_is_invalid($old_password) ||
        input_is_invalid($new_password)
    ) {
        respondBadRequest("All fields are required.");
        exit;
    }

    else if (strlen($new_password) < 6) {
        respondBadRequest("New password must be at least 6 characters.");
        exit;
    }

    $checkUser = $connect->prepare("SELECT password FROM users WHERE user_id = ?");
    $checkUser->bind_param("i", $user_id);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows == 0) {
        respondBadRequest("User not found.");
        exit;
    }

    $user = $result->fetch_assoc();

    $stored = $user['password'];
    $oldOk = password_verify($old_password, $stored) || hash_equals((string)$stored, (string)$old_password);
    if (!$oldOk) {
        respondBadRequest("Old password is incorrect.");
        exit;
    }

    $hashedNewPassword = password_hash($new_password, PASSWORD_DEFAULT);
    $updatePassword = $connect->prepare("UPDATE users
        SET password = ?
        WHERE user_id = ?
    ");

    $updatePassword->bind_param("si", $hashedNewPassword, $user_id);
    $updatePassword->execute();

    if ($updatePassword->affected_rows > 0) {
        $accesstoken = getTokenToSendAPI($user_id);
        respondOK(
            ["access_token" => $accesstoken],
            "Password updated successfully"
        );
    } else {
        respondBadRequest("Password update failed.");
    }

} else {
    respondBadRequest("Invalid request. All fields are required.");
}
?>
