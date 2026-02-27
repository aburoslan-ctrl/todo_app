<?php

$method="POST";
$cache="no-cache";
include "../../head.php";


if (isset($_POST['user_id'], $_POST['fname'], $_POST['lname'], $_POST['password'])) {

    $user_id = cleanme($_POST['user_id']);
    $fname  = cleanme($_POST['fname']);
    $lname  = cleanme($_POST['lname']);
    $password = cleanme($_POST['password']);

    // Validation
    if (
        input_is_invalid($user_id) ||
        input_is_invalid($fname) ||
        input_is_invalid($lname) ||
        input_is_invalid($password)
    ) {
        respondBadRequest("All fields are required.");
        exit;
    }

    // user_id must be numeric
    else if (!is_numeric($user_id)) {
        respondBadRequest("User ID must be numeric.");
        exit;
    }

    // f_name minimum length
    else if (strlen($fname) < 3) {
        respondBadRequest("First name must be at least 3 characters.");
        exit;
    }

    // l_name minimum length
    else if (strlen($lname) < 3) {
        respondBadRequest("Last name must be at least 3 characters.");
        exit;
    }

    // Minimum password length
    else if (strlen($password) < 6) {
        respondBadRequest("New password must be at least 6 characters.");
        exit;
    }



    // Check if user exists
    $checkUser = $connect->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $checkUser->bind_param("i", $user_id);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows == 0) {
        respondBadRequest("User not found.");
        exit;
    }

    // Update profile
    $updateProfile = $connect->prepare("
        UPDATE users 
        SET fname = ?, lname = ?, password = ?
        WHERE user_id = ?
    ");

    $updateProfile->bind_param("sssi", $fname, $lname, $password, $user_id);
    $updateProfile->execute();


         // Fetch the newly updated user details
    if ($updateProfile->affected_rows > 0) {
       
        $getUser = $connect->prepare("SELECT user_id, fname, lname
            FROM users 
            WHERE user_id = ?
        ");
        $getUser->bind_param("i", $user_id);
        $getUser->execute();
        $userDetails = $getUser->get_result()->fetch_assoc();
            $user_id=$userDetails["user_id"];
            $accesstoken=getTokenToSendAPI($user_id);

        respondOK($accesstoken, "Profile updated successfully");
    } else {
        respondBadRequest("No changes made or update failed.");
    }

} else {
    respondBadRequest("Invalid request. All fields are required.");
}




?>


