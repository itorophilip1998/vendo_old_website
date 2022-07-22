<?php

    include("configuration.php");
    include("db.php");
    include("translate.php");
    include("utils.php");
    require_once('lib/commissions.php');

    session_start(); // start or continue a session
    $loggedInAsUserId = $_SESSION["loggedInAsUserId"];
    if (!$loggedInAsUserId) {
        $result["message"] = "Your session has expired, please log in again.";
        $result["code"] = -10;
        $jsonOut=json_encode($result);		
        die($jsonOut);            
    }

    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
    $rankStart = intval($_REQUEST["rankStart"]);
    $rankEnd = intval($_REQUEST["rankEnd"]);

    $leaderboard = [];
    if($rankStart <= 0 || $rankStart <=0 || $rankStart > $rankEnd) {
    } else {
        $leaderboard = getLeaderboard($pdo, $rankStart, $rankEnd);
    }

    foreach($leaderboard as &$user) {
        if ($user["max_career_level"] > 0)
        {
            $user["level_name"] = Commissions::getCareerLevelName($user["max_career_level"]);
        }
        else
        {
            $user["level_name"] = Commissions::getNameAffiliateLevel($user["max_affiliate_level"]);
        }
    }

    ob_end_clean();

    echo json_encode([
        'leaderboard' => $leaderboard,
        'code' => 200
    ]);
?>