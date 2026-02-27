<?php
$method="POST";
$cache="no-cache";
include "../../head.php";


if (isset($_POST['task_id'], $_POST['status'])) {

    $task_id = cleanme($_POST['task_id']);
    $status  = cleanme($_POST['status']);

    // Validation: Empty check
    if (
        input_is_invalid($task_id) ||
        input_is_invalid($status)
    ) {
        respondBadRequest("Task ID and Status are required.");
        exit;
    }

    // Validation: task_id must be numeric
    else if (!is_numeric($task_id)) {
        respondBadRequest("Task ID must be a number.");
        exit;
    }

    // Validation: task_id must be greater than 0
    else if ($task_id <= 0) {
        respondBadRequest("Task ID must be greater than 0.");
        exit;
    }

    // Validation: Optional — restrict allowed status values
    $allowed_status = ["complete", "pending"];

    if (!in_array($status, $allowed_status)) {
        respondBadRequest("Invalid status value.");
        exit;
    }

    // Check if task exists
    $checkTask = $connect->prepare("SELECT task_id FROM tasks WHERE task_id = ?");
    $checkTask->bind_param("i", $task_id);
    $checkTask->execute();
    $result = $checkTask->get_result();

    if ($result->num_rows == 0) {
        respondBadRequest("Task ID not found.");
        exit;
    }

    // Update status
    $updateStatus = $connect->prepare("UPDATE tasks 
        SET status = ?
        WHERE task_id = ?
    ");

    $updateStatus->bind_param("si", $status, $task_id);
    $updateStatus->execute();

    if ($updateStatus->affected_rows > 0) {
         $accesstoken=getTokenToSendAPI($task_id);
        respondOK(["access_token" => $accesstoken], "Task status updated successfully");
    } else {
        respondBadRequest("Failed to update task status.");
    }

} else {
    respondBadRequest("Invalid request. Task ID and Status are required.");
}



?>


