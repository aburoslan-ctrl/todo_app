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
        $getdataemail =  $connect->prepare("SELECT user_id, password FROM users where user_id=?");
        $getdataemail->bind_param("i",$user_id);
        $getdataemail->execute();
        $getresultemail = $getdataemail->get_result();
        if( $getresultemail->num_rows> 0){
            $row = $getresultemail->fetch_assoc();
            $stored = $row['password'];
            $ok = false;
            if (password_verify($password, $stored)) {
                $ok = true;
            } elseif (hash_equals((string)$stored, (string)$password)) {
                // Legacy plaintext: upgrade to hash on successful login.
                $ok = true;
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $connect->prepare("UPDATE users SET password=? WHERE user_id=?");
                $upd->bind_param("si", $newHash, $user_id);
                $upd->execute();
            }
            if ($ok) {
                $accesstoken=getTokenToSendAPI($user_id);
                respondOK(["access_token"=>$accesstoken],"Login successful");
            } else {
                respondBadRequest("Invalid user ID or password");
            }
        }else{ respondBadRequest("Invalid user ID or password"); }
 }
}else{
   respondBadRequest("Invalid request. User ID and password are required.");
}
 
?>