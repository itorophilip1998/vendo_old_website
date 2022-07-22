<?php

    require_once('./lib/protoncapitalmarkets.php');
    use Brokers\ProtonCapitalMarketsBroker;

    $result=[];

	try {            
        include("configuration.php");
        include("db.php");
        include("utils.php");

        session_start(); // start or continue a session
        $loggedInAsSponsorId = $_SESSION["loggedInAsUserId"];
        if (!$loggedInAsSponsorId) {
            $result["message"] = "Your session has expired, please log in again.";
            $result["code"] = -2;
            $jsonOut=json_encode($result);		
		    die($jsonOut);            
        }
        
        $pdo = getDatabase();
        
        //ensure actual user data from db
        include './bo_read_user_logged_in.php';

        //at this time $user should be valid and contain valid user
        
        $date_begin = $user['last_trading_history_end_date']; //last end should be lower boundary
        $date_end = date('Y-m-d'); //today (YYYY-MM-DD) format

        $broker = new ProtonCapitalMarketsBroker(PROTONCAPITALMARKETS_SERVERNAME, PROTONCAPITALMARKETS_AUTHCODE);
        $response = $broker->getTradeHistory($user['id'], $date_begin, $date_end);
        
        $success = updateOrderHistory($pdo, $user['id'], $response);
        if ($success != false) {
            updateUser($pdo, $user['id'], array('last_trading_history_end_date' => $date_end));

            $result["message"] = "Ok";
            $result["code"] = 0;
            $jsonOut = json_encode($result);
            die($jsonOut);
        }

        $result["message"] = "User trading history could not be updated!";
        $result["code"] = -1;
        $jsonOut=json_encode($result);		
        die($jsonOut);            

    } catch (Exception $e) {
        $result["message"] = $e->getMessage();
        $result["code"] = -1;
		$jsonOut=json_encode($result);		
		die($jsonOut);
	}
