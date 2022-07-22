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
        $pdo->beginTransaction();

        do{
            $temporaryCode = randomCode();

            $sql = "SELECT * FROM TemporaryEntryCodes WHERE code=:temporaryCode;";
            $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->bindParam(':temporaryCode', $temporaryCode);
            if (!$sth -> execute()) {
                $msg = "Error: ".$sth -> errorInfo()[2];
                error_log($msg);
                $result["code"] = -2;
                $result["message"] = $msg;
                $jsonOut=json_encode($result);
            }

            $rowDb = $sth -> fetch(PDO:: FETCH_ASSOC);
        } while ($rowDb);
        

        $sql = "INSERT INTO TemporaryEntryCodes (date, code, sponsor_user_id) VALUES (NOW(), :code, :userId)";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':userId', $userId);
        $sth->bindParam(':code', $temporaryCode);
        if (!$sth -> execute()) {
            $msg = "Error: ".$sth -> errorInfo()[2];
            error_log($msg);
            $result["code"] = -3;
            $result["message"] = $msg;
            $jsonOut=json_encode($result);
        }

        $pdo->commit();

        $result["message"] = "Ok";
        $result["temporaryCode"] = $temporaryCode;
        $result["code"] = 1;
		$jsonOut=json_encode($result);
		die($jsonOut);
		
	} catch (Exception $e) {
        if ($pdo->inTransaction())
            $pdo->rollback();

        $result["message"] = $e->getMessage();
		$jsonOut=json_encode($result);		
		die($jsonOut);
    }
    
    function randomCode()
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = array(); //remember to declare $pass as an array
        $code = [];
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 6; $i++) {
            $n = rand(0, $alphaLength);
            $code[] = $alphabet[$n];
            //if ($i == 3 || $i == 7)
            //$code[] = "-";
        }
        return implode($code); //turn the array into a string
    }
?>