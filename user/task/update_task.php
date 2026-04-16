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

if (isset($_POST['task_id'], $_POST['what_to_do'], $_POST['start_time'], $_POST['end_time'], $_POST['status'])) {

    $task_id    = cleanme($_POST['task_id']);
    $what_to_do = cleanme($_POST['what_to_do']);
    $start_time = cleanme($_POST['start_time']);
    $end_time   = cleanme($_POST['end_time']);
    $status     = cleanme($_POST['status']);

    if (
        input_is_invalid($task_id) ||
        input_is_invalid($what_to_do) ||
        input_is_invalid($start_time) ||
        input_is_invalid($end_time) ||
        input_is_invalid($status)
    ) {
        respondBadRequest("All fields are required.");
        exit;
    }

    else if (!is_numeric($task_id) || $task_id <= 0) {
        respondBadRequest("Task ID must be a valid number.");
        exit;
    }

    else if (strlen($what_to_do) < 3) {
        respondBadRequest("Task must be at least 3 characters.");
        exit;
    }

    $checkTaskId = $connect->prepare("SELECT user_id FROM tasks WHERE task_id = ?");
    $checkTaskId->bind_param("i", $task_id);
    $checkTaskId->execute();
    $taskIdResult = $checkTaskId->get_result();

    if ($taskIdResult->num_rows == 0) {
        respondBadRequest("Task not found.");
        exit;
    }

    $taskRow = $taskIdResult->fetch_assoc();
    if ((int)$taskRow['user_id'] !== (int)$user_id) {
        respondForbiddenAuthorized("Not your task.");
        exit;
    }

    $checkTask = $connect->prepare("SELECT task_id FROM tasks WHERE what_to_do = ? AND task_id != ?");
    $checkTask->bind_param("si", $what_to_do, $task_id);
    $checkTask->execute();
    $taskResult = $checkTask->get_result();

    if ($taskResult->num_rows > 0) {
        respondBadRequest("This task already exists.");
        exit;
    }

    $updateTask = $connect->prepare("UPDATE tasks
        SET what_to_do = ?,
            start_time = ?,
            end_time = ?,
            status = ?
        WHERE task_id = ?
    ");

    $updateTask->bind_param("ssssi", $what_to_do, $start_time, $end_time, $status, $task_id);
    $updateTask->execute();

    if ($updateTask->affected_rows > 0) {
        $accesstoken = getTokenToSendAPI($user_id);
        respondOK(["access_token" => $accesstoken], "Task updated successfully");
    } else {
        respondBadRequest("No changes made or update failed.");
    }

} else {
    respondBadRequest("Invalid request. All fields are required.");
}
?>
