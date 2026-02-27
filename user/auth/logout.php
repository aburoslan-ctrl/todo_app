<?php
$method="POST";
$cache="no-cache";
include "../../head.php";

// start session
session_start();

if(isset($_POST['user_id'])){

    $user_id = cleanme($_POST['user_id']);

    // validation
    if(input_is_invalid($user_id)){
        respondBadRequest("User ID is required");
    } 
    else if(!is_numeric($user_id)){
        respondBadRequest("User ID must be numeric");
    } 
    else {

        

            respondOK(["user_id"=>$user_id], "Logout successful");

    

}}
?>