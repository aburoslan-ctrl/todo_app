<?php
$method="POST";
$cache="no-cache";
include "../../head.php";

// user id and password
if(isset($_POST['user_id']) && isset($_POST['password'])){
    $user_id=cleanme($_POST['user_id']);
    $password=cleanme($_POST['password']);
    //validation
    if(input_is_invalid($user_id) || input_is_invalid($password)){
        respondBadRequest("User ID and password are required");
    }else if(!is_numeric($user_id)){ 
        respondBadRequest("User ID must be numeric");
    }else {
        $getdataemail =  $connect->prepare("SELECT * FROM users where user_id=? and password=?"); 
        $getdataemail->bind_param("is",$user_id,$password);
        $getdataemail->execute();
        $getresultemail = $getdataemail->get_result();
        if( $getresultemail->num_rows> 0){
          respondOK(["user_id"=>$user_id],"Login successful");
        }else{ respondBadRequest(" user not found"); } 
 }
}else{
   respondBadRequest("Invalid request. User ID and password are required.");
}
 
?>