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
        
        $broker = new ProtonCapitalMarketsBroker(PROTONCAPITALMARKETS_SERVERNAME, PROTONCAPITALMARKETS_AUTHCODE);
        $response = $broker->getTradeInfo($user['id']);

        //update user account infos
        updateUser($pdo, $user['id'], array('AccountNumber' => $response['account_number'], 'balance' => $response['TradeInfo']['Balance'], 'equity' => $response['TradeInfo']['Equity']));
        
        //update orders
        $openorders = empty($response['Orders'][0]['OpenOrders']) ? array() : $response['Orders'][0]['OpenOrders'];
        updateOpenOrders($pdo, $user['id'], $openorders);

        $pendingorders = empty($response['Orders'][1]['PendingOrders']) ? array() : $response['Orders'][1]['PendingOrders'];
        updatePendingOrders($pdo, $user['id'], $pendingorders);
        
        $result["info"] = $response;
        $result["message"] = "Ok";
        $result["code"] = 0;
        $jsonOut = json_encode($result);
        die($jsonOut);

    } catch (Exception $e) {
        $result["message"] = $e->getMessage();
        $result["code"] = -1;
		$jsonOut=json_encode($result);		
		die($jsonOut);
	}
