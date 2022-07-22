<?php

    require_once('./lib/protoncapitalmarkets.php');
    use Brokers\ProtonCapitalMarketsBroker;

    $result=[];

	try {            
        include("configuration.php");
        include("db.php");
        include("utils.php");

        $pdo = getDatabase();

        $pamm_user_id = 1;

        $broker = new ProtonCapitalMarketsBroker(PROTONCAPITALMARKETS_SERVERNAME, PROTONCAPITALMARKETS_AUTHCODE);
        $response = $broker->getTradeInfo($pamm_user_id);

        //update user account infos
        updateUser($pdo, $pamm_user_id, array('AccountNumber' => $response['account_number'], 'balance' => $response['TradeInfo']['Balance'], 'equity' => $response['TradeInfo']['Equity']));

        //update orders
        $openorders = empty($response['Orders'][0]['OpenOrders']) ? array() : $response['Orders'][0]['OpenOrders'];
        updateOpenOrders($pdo, $pamm_user_id, $openorders);

        $pendingorders = empty($response['Orders'][1]['PendingOrders']) ? array() : $response['Orders'][1]['PendingOrders'];
        updatePendingOrders($pdo, $pamm_user_id, $pendingorders);

    } catch (Exception $e) {	
        error_log($e->getMessage());
		die();
	}