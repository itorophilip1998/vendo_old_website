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
    
    $pdo = getDatabase();

    include 'bo_read_user_logged_in.php';
    // is logged in

    $history = getProfitHistory($user['id'], $user['language']);
  
    $statistics = getBankedProfits($user['id']);

    $balance = $user['balance'];
    $automation = $user['automation'];
    //show open orders only if user participates in the trades (positive balance and automation turned on)
    if (($balance > 0) && (strtolower($automation) == "on"))
    {
        $openOrders = getOpenOrders($user['id']);
    }

    echo json_encode([
        'code' => 200,
        'labels' => $history['labels'],
        'data' => $history['data'],
        'statistics' => $statistics,
        'openOrderRows' => $openOrders
    ]);

    die();
?>