<?php

    $result=[];

	try {            
        include("configuration.php");
        include("db.php");

		$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); 
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        session_start(); // start or continue a session
        $loggedInAsSponsorId = $_SESSION["loggedInAsUserId"];
        if (!$loggedInAsSponsorId) {
            $result["message"] = "Your session has expired, please log in again.";
            $result["code"] = -2;
            $jsonOut=json_encode($result);		
		    die($jsonOut);            
        }
    
        include('bo_read_user_logged_in.php');
        // is logged in and user loaded
    
        //
        $language = trim($_REQUEST["language"]);

        //
        $result["message"] = "";

        if(!$language)
            $result["message"] .= "Parameter language missing. ";
        //if($ownMoney != "0" && $ownMoney != "1")
        //    $result["message"] .= "Parameter ownMoney missing or faulty value. ";
        //if($existenceThreat != "0" && $existenceThreat != "1")
        //    $result["message"] .= "Parameter existenceThreat missing. ";

        $result["message"] = trim($result["message"]);
        if($result["message"]) {
            $result["code"] = -1;
            $jsonOut=json_encode($result);		
		    die($jsonOut);
        }

        //
        $sql = "UPDATE User SET language=:language WHERE id=:userId";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':userId', $user["id"]);
        $sth->bindParam(':language', $language);
        if (!$sth -> execute()) {
            $msg = "Error: ".$sth -> errorInfo()[2];
            error_log($msg);
            $result["code"] = -3;
            $result["message"] = $msg;
            $jsonOut=json_encode($result);
        }

        $result["message"] = "Ok";
        $result["code"] = 1;
		$jsonOut=json_encode($result);
		die($jsonOut);
		
	} catch (Exception $e) {
        $result["message"] = $e->getMessage();
		$jsonOut=json_encode($result);		
		die($jsonOut);
	}
?>