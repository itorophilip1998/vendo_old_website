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

    if (!$user['is_admin'])
    {
        $result["message"] = "You are not an admin.";
        $result["code"] = -11;
        $jsonOut=json_encode($result);		
        die($jsonOut);            
    }
    
    $searchString = trim($_REQUEST["searchString"]);

    $users = searchUsers($pdo, $searchString);

    ob_end_clean();

    echo json_encode([
        'users' => $users,
        'code' => 200
    ]);
?>