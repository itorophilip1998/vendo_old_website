<?php

    $result=[];

    include("configuration.php");
    include("db.php");

    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    session_start(); // start or continue a session
    $loggedInAsSponsorId = $_SESSION["loggedInAsUserId"];
    if (!$loggedInAsSponsorId) {
        $result["message"] = "Your session has expired, please log in again.";
        $result["code"] = -1;
        $jsonOut=json_encode($result);		
        die($jsonOut);
    }
    
    include 'bo_read_user_logged_in.php';
	// is logged in

	try {            

        //
        $userId = trim($_REQUEST["userId"]);

        //
        $result["message"] = "";

        if(!$userId)
            $result["message"] .= "Parameter userId missing. ";

        $result["message"] = trim($result["message"]);
        if($result["message"]) {
            $result["code"] = -1;
            $jsonOut=json_encode($result);		
		    die($jsonOut);
        }

        //
        $timeCodeValidFrom = time() - DURATION_TEMPORARY_CODE_VALID_SECONDS;
        //$sql = "SELECT * FROM TemporaryEntryCodes WHERE sponsor_user_id=:userId AND `date`>=:timeValidFrom AND user_id IS NULL ORDER BY date DESC;";
        //$sql = "SELECT * FROM TemporaryEntryCodes WHERE sponsor_user_id=:userId AND user_id IS NULL ORDER BY date DESC;";
        $sql = "SELECT tec.*, u.given_name, u.sur_name, u.broker_registration_complete FROM TemporaryEntryCodes tec LEFT JOIN User u ON tec.user_id = u.id WHERE tec.sponsor_user_id=:userId ORDER BY tec.date DESC;";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':userId', $userId);
        //$sth->bindParam(':timeValidFrom', date("Y-m-d H:i:s", $timeCodeValidFrom));
        if (!$sth -> execute()) {
            $msg = "Error: ".$sth -> errorInfo()[2];
            error_log($msg);
            $result["code"] = -2;
            $result["message"] = $msg;
            $jsonOut=json_encode($result);
        }

        $codes = $sth -> fetchAll(PDO:: FETCH_ASSOC);
        
        $result["message"] = "Ok";
        $result["temporaryCodes"] = $codes;
        $result["code"] = 1;
		$jsonOut=json_encode($result);
		die($jsonOut);
		
	} catch (Exception $e) {

        $result["message"] = $e->getMessage();
		$jsonOut=json_encode($result);		
		die($jsonOut);
    }
    
?>