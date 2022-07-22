<?php

try {
    include("configuration.php");
    include("db.php");
    include("utils.php");

    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    session_start(); // start or continue a session
    $loggedInAsSponsorId = $_SESSION["loggedInAsUserId"];
    if (!$loggedInAsSponsorId) {
        http_response_code(401);
        $result["message"] = "Your session has expired, please log in again.";
        $result["code"] = -1;
        $jsonOut=json_encode($result);		
        die($jsonOut);
    }

    include('bo_read_user_logged_in.php');
    // is logged in and user loaded

    $sponsorTree = loadSponsorTree($pdo, $_REQUEST["userId"], true, 1);

    echo (createHtmlTree($sponsorTree, $_REQUEST['debug']));
} catch (Exception $e) {
    echo "Error";
}

?>
