<?php
$method="POST";
$cache="no-cache";
include "../../head.php";



if (isset($_POST['task_id'])) {

    $task_id = cleanme($_POST['task_id']);

    // Check if empty
    if (input_is_invalid($task_id)) {
        respondBadRequest("Task ID is required.");
        exit;
    }

    // Must be numeric
    else if (!is_numeric($task_id)) {
        respondBadRequest("Task ID must be a number.");
        exit;
    }

    // Must be greater than 0
    else if ($task_id <= 0) {
        respondBadRequest("Task ID must be greater than 0.");
        exit;
    }

    // Check if task exists
    $checkTask = $connect->prepare("SELECT task_id FROM tasks WHERE task_id = ?");
    $checkTask->bind_param("i", $task_id);
    $checkTask->execute();
    $result = $checkTask->get_result();

    if ($result->num_rows == 0) {
        respondBadRequest("Task not found.");
        exit;
    }

    // Delete Task
    $deleteTask = $connect->prepare("DELETE FROM tasks WHERE task_id = ?");
    $deleteTask->bind_param("i", $task_id);
    $deleteTask->execute();

    if ($deleteTask->affected_rows > 0) {
        respondOK(["task_id" => $task_id], "Task deleted successfully");
    } else {
        respondBadRequest("Failed to delete task.");
    }

} else {
    respondBadRequest("Invalid request. Task ID is required.");
}


?>


