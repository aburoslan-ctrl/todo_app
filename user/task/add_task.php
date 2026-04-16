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

if (isset($_POST['what_to_do'], $_POST['start_time'], $_POST['end_time'], $_POST['status'])) {

    $what_to_do = cleanme($_POST['what_to_do']);
    $start_time = cleanme($_POST['start_time']);
    $end_time   = cleanme($_POST['end_time']);
    $status     = cleanme($_POST['status']);

    if (
        input_is_invalid($what_to_do) ||
        input_is_invalid($start_time) ||
        input_is_invalid($end_time) ||
        input_is_invalid($status)
    ) {
        respondBadRequest("All fields are required.");
        exit;
    }

    else if (strlen($what_to_do) < 3) {
        respondBadRequest("Task must be at least 3 characters.");
        exit;
    }

    else {
        $checkTask = $connect->prepare("SELECT task_id FROM tasks WHERE what_to_do = ?");
        $checkTask->bind_param("s", $what_to_do);
        $checkTask->execute();
        $taskResult = $checkTask->get_result();

        if ($taskResult->num_rows > 0) {
            respondBadRequest("This task already exists.");
            exit;
        }
    }

    $insertTask = $connect->prepare("
        INSERT INTO tasks
        (what_to_do, user_id, start_time, end_time, status)
        VALUES (?, ?, ?, ?, ? )
    ");

    $insertTask->bind_param("sisss", $what_to_do, $user_id, $start_time, $end_time, $status);
    $insertTask->execute();

    if ($insertTask->affected_rows > 0) {
        $accesstoken = getTokenToSendAPI($user_id);
        respondOK(["access_token" => $accesstoken], "Task added successfully");
    } else {
        respondBadRequest("Failed to add task.");
    }

} else {
    respondBadRequest("Invalid request. All fields are required.");
}
?>
