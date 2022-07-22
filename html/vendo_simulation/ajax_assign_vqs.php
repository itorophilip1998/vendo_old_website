<?php

    include("configuration.php");
    include("db.php");
    include("translate.php");
    include("utils.php");
    require_once(__DIR__  . "/lib/commissions.php");

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

    $userId = trim($_REQUEST["userId"]);
    $vqs = trim($_REQUEST["vqs"]);

    $commissions_calculator = new Commissions;
    $commissions_calculator->addVQManual($userId, $vqs);

    ob_end_clean();

    echo json_encode([
        'userId' => $userId,
        'vqs' => $vqs,
        'message' => "Assigned ".$vqs." to ".$userId,
        'code' => 200
    ]);
?>