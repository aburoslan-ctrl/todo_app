<?php
$method="POST";
$cache="no-cache";
include "../../head.php";


if (isset($_POST['what_to_do'], $_POST['user_id'], $_POST['start_time'], $_POST['end_time'], $_POST['status'])) {

    $what_to_do =cleanme($_POST['what_to_do']);
    $user_id    = cleanme($_POST['user_id']);
    $start_time = cleanme($_POST['start_time']);
    $end_time   = cleanme($_POST['end_time']);
    $status     = cleanme($_POST['status']);

    //   Validation  to check if its Empty 
    if (
        input_is_invalid($what_to_do) ||
        input_is_invalid($user_id) ||
        input_is_invalid($start_time) ||
        input_is_invalid($end_time) ||
        input_is_invalid($status)
    ) {
        respondBadRequest("All fields are required.");
        exit;
    }

    //  Validation  for user_id: Must be numeric
    else if (!is_numeric($user_id)) {
        respondBadRequest("User ID must be a number.");
        exit;
    }

    //  Validation  for user_id: Must be greater than 0
    else if ($user_id <= 0) {
        respondBadRequest("User ID must be greater than 0.");
        exit;
    }

    //  Validation  for what_to_do: Minimum length
    else if (strlen($what_to_do) < 3) {
        respondBadRequest("Task must be at least 3 characters.");
        exit;
    }

    // Validation for what_to_do: Prevent repetition
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

    // Check if user exists
    $checkUser = $connect->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $checkUser->bind_param("i", $user_id);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows == 0) {
        respondBadRequest("User ID not found.");
        exit;
    }
    

    // Insert Task
    $insertTask = $connect->prepare("
        INSERT INTO tasks 
        (what_to_do, user_id, start_time, end_time, status) 
        VALUES (?, ?, ?, ?, ? )
    ");

    $insertTask->bind_param("sisss", $what_to_do, $user_id, $start_time, $end_time, $status);
    $insertTask->execute();

    if ($insertTask->affected_rows > 0) {
        respondOK(["user_id" => $user_id], "Task added successfully");
    } else {
        respondBadRequest("Failed to add task.");
    }

} else {
    respondBadRequest("Invalid request. All fields are required.");
}






?>


