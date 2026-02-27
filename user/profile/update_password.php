<?php

$method="POST";
$cache="no-cache";
include "../../head.php";

if (isset($_POST['user_id'], $_POST['old_password'], $_POST['new_password'])) {

    $user_id      = cleanme($_POST['user_id']);
    $old_password = cleanme($_POST['old_password']);
    $new_password = cleanme($_POST['new_password']);

    // Validation
    if (
        input_is_invalid($user_id) ||
        input_is_invalid($old_password) ||
        input_is_invalid($new_password)
    ) {
        respondBadRequest("All fields are required.");
        exit;
    }

    else if (!is_numeric($user_id)) {
        respondBadRequest("User ID must be numeric.");
        exit;
    }

    else if (strlen($new_password) < 6) {
        respondBadRequest("New password must be at least 6 characters.");
        exit;
    }

    // Check if user exists and verify old password
    $checkUser = $connect->prepare("SELECT password FROM users WHERE user_id = ?");
    $checkUser->bind_param("i", $user_id);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows == 0) {
        respondBadRequest("User not found.");
        exit;
    }

    $user = $result->fetch_assoc();

    // Compare old password (plain text - same as your system style)
    if ($user['password'] !== $old_password) {
        respondBadRequest("Old password is incorrect.");
        exit;
    }

    // Update password
    $updatePassword = $connect->prepare("UPDATE users 
        SET password = ?
        WHERE user_id = ?
    ");

    $updatePassword->bind_param("si", $new_password, $user_id);
    $updatePassword->execute();

    if ($updatePassword->affected_rows > 0) {

        respondOK(
            ["user_id"=> $user_id],
            "Password updated successfully"
        );

    } else {
        respondBadRequest("Password update failed.");
    }

} else {
    respondBadRequest("Invalid request. All fields are required.");
}

?>