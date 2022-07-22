<?php
    include("configuration.php");
    include("db.php");
    include("translate.php");
    include("utils.php");

    session_start(); // start or continue a session
    $loggedInAsSponsorId = $_SESSION["loggedInAsUserId"];
    if (!$loggedInAsSponsorId) {
        $result["message"] = "Your session has expired, please log in again.";
        $result["code"] = -10;
        $jsonOut=json_encode($result);		
        die($jsonOut);            
    }
    
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    include 'bo_read_user_logged_in.php';
    // is logged in

    $oldPassword = $_POST['oldPassword'];
    $newPassword = $_POST['newPassword'];
    $newPasswordRepeat = $_POST['newPasswordRepeat'];

    if(!isset($oldPassword) || !isset($newPassword) || !isset($newPasswordRepeat)) {
        echo json_encode([
            'message' => localize('bo_password_change_failed', $user['language']),
            'code' => -2
        ]);
    } else {
        $passwordOk = password_verify($oldPassword, $user['password']);
        if($passwordOk) {
            $success = changeUserPassword($user['email'], $newPassword, $user['id']);
            if($success) {
                echo json_encode([
                    'message' => localize('bo_password_changed_successfully', $user['language']),
                    'code' => 200
                ]);
            } else {
                echo json_encode([
                    'message' => localize('bo_password_change_failed', $user['language']),
                    'code' => -2
                ]);
            }
        } else {
            echo json_encode([
                'message' => localize('bo_password_false_password', $user['language']),
                'code' => -2
            ]);
        }
    }

   

?>