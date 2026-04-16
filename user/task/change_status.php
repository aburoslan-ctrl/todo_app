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

if (isset($_POST['task_id'], $_POST['status'])) {

    $task_id = cleanme($_POST['task_id']);
    $status  = cleanme($_POST['status']);

    if (
        input_is_invalid($task_id) ||
        input_is_invalid($status)
    ) {
        respondBadRequest("Task ID and Status are required.");
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

    $allowed_status = ["complete", "pending"];

    if (!in_array($status, $allowed_status)) {
        respondBadRequest("Invalid status value.");
        exit;
    }

    $checkTask = $connect->prepare("SELECT user_id FROM tasks WHERE task_id = ?");
    $checkTask->bind_param("i", $task_id);
    $checkTask->execute();
    $result = $checkTask->get_result();

    if ($result->num_rows == 0) {
        respondBadRequest("Task ID not found.");
        exit;
    }
    $taskRow = $result->fetch_assoc();
    if ((int)$taskRow['user_id'] !== (int)$user_id) {
        respondForbiddenAuthorized("Not your task.");
        exit;
    }

    $updateStatus = $connect->prepare("UPDATE tasks
        SET status = ?
        WHERE task_id = ?
    ");

    $updateStatus->bind_param("si", $status, $task_id);
    $updateStatus->execute();

    if ($updateStatus->affected_rows > 0) {
        $accesstoken = getTokenToSendAPI($user_id);
        respondOK(["access_token" => $accesstoken], "Task status updated successfully");
    } else {
        respondBadRequest("Failed to update task status.");
    }

} else {
    respondBadRequest("Invalid request. Task ID and Status are required.");
}
?>
