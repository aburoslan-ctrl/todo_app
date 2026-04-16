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

if (isset($_POST['task_id'])) {

    $task_id = cleanme($_POST['task_id']);

    if (input_is_invalid($task_id)) {
        respondBadRequest("Task ID is required.");
        exit;
    }

    else if (!is_numeric($task_id)) {
        respondBadRequest("Task ID must be a number.");
        exit;
    }

    else if ($task_id <= 0) {
        respondBadRequest("Task ID must be greater than 0.");
        exit;
    }

    $checkTask = $connect->prepare("SELECT user_id FROM tasks WHERE task_id = ?");
    $checkTask->bind_param("i", $task_id);
    $checkTask->execute();
    $result = $checkTask->get_result();

    if ($result->num_rows == 0) {
        respondBadRequest("Task not found.");
        exit;
    }

    $taskRow = $result->fetch_assoc();
    if ((int)$taskRow['user_id'] !== (int)$user_id) {
        respondForbiddenAuthorized("Not your task.");
        exit;
    }

    $deleteTask = $connect->prepare("DELETE FROM tasks WHERE task_id = ?");
    $deleteTask->bind_param("i", $task_id);
    $deleteTask->execute();

    if ($deleteTask->affected_rows > 0) {
        $accesstoken = getTokenToSendAPI($user_id);
        respondOK(["access_token" => $accesstoken], "Task deleted successfully");
    } else {
        respondBadRequest("Failed to delete task.");
    }

} else {
    respondBadRequest("Invalid request. Task ID is required.");
}
?>
