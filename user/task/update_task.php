<?php
$method="POST";
$cache="no-cache";
include "../../head.php";

if (isset($_POST['task_id'], $_POST['what_to_do'], $_POST['user_id'], $_POST['start_time'], $_POST['end_time'], $_POST['status'])) {

    $task_id   = cleanme($_POST['task_id']);
    $what_to_do = cleanme($_POST['what_to_do']);
    $user_id    = cleanme($_POST['user_id']);
    $start_time = cleanme($_POST['start_time']);
    $end_time   = cleanme($_POST['end_time']);
    $status     = cleanme($_POST['status']);

    //  Validation 1: Empty check
    if (
        input_is_invalid($task_id) ||
        input_is_invalid($what_to_do) ||
        input_is_invalid($user_id) ||
        input_is_invalid($start_time) ||
        input_is_invalid($end_time) ||
        input_is_invalid($status)
    ) {
        respondBadRequest("All fields are required.");
        exit;
    }

    //  Validation 2: task_id must be numeric
    else if (!is_numeric($task_id) || $task_id <= 0) {
        respondBadRequest("Task ID must be a valid number.");
        exit;
    }

    //  Validation  user_id must be numeric
    else if (!is_numeric($user_id) || $user_id <= 0) {
        respondBadRequest("User ID must be a valid number.");
        exit;
    }

    //  Validation  Minimum task length
    else if (strlen($what_to_do) < 3) {
        respondBadRequest("Task must be at least 3 characters.");
        exit;
    }

    //  Check if task exists
    $checkTaskId = $connect->prepare("SELECT task_id FROM tasks WHERE task_id = ?");
    $checkTaskId->bind_param("i", $task_id);
    $checkTaskId->execute();
    $taskIdResult = $checkTaskId->get_result();

    if ($taskIdResult->num_rows == 0) {
        respondBadRequest("Task not found.");
        exit;
    }

    // Prevent duplicate task 
    $checkTask = $connect->prepare("SELECT task_id FROM tasks WHERE what_to_do = ? AND task_id != ?");
    $checkTask->bind_param("si", $what_to_do, $task_id);
    $checkTask->execute();
    $taskResult = $checkTask->get_result();

    if ($taskResult->num_rows > 0) {
        respondBadRequest("This task already exists.");
        exit;
    }

    //  Check if user exists
    $checkUser = $connect->prepare("SELECT task_id FROM tasks WHERE task_id = ?");
    $checkUser->bind_param("i", $task_id);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows == 0) {
        respondBadRequest("User ID not found.");
        exit;
    }

    // UPDATE TASK
    $updateTask = $connect->prepare("UPDATE tasks 
        SET what_to_do = ?, 
            user_id = ?, 
            start_time = ?, 
            end_time = ?, 
            status = ?
        WHERE task_id = ?
    ");

    $updateTask->bind_param("sisssi", $what_to_do, $user_id, $start_time, $end_time, $status, $task_id);
    $updateTask->execute();

    if ($updateTask->affected_rows > 0) {
        $accesstoken=getTokenToSendAPI($task_id);
        respondOK(["access_token" => $accesstoken], "Task updated successfully");
    } else {
        respondBadRequest("No changes made or update failed.");
    }

} else {
    respondBadRequest("Invalid request. All fields are required.");
}

?>