<?php
    require_once("configuration.php");
    require_once("db.php");
    require_once("translate.php");
    require_once("utils.php");
    require_once("bitcoin.php");

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

    include 'bo_read_user_logged_in.php';
    // is logged in

	if (!$user['is_admin'])
	{
		//silent fail 
		redirect(ROOT_URL.'bo_main.php');
	}

    $obt_id = getRequest('transactionId');

    $obt = Bitcoin::getOpenBuyingTransaction($obt_id);

    $post = array(
        'txn_id' => $obt['transaction_id'],
        'item_name' => $obt['product'],
        'item_number' => '',
        'amount1' => $obt['amount'],
        'amount2' => $obt['api_amount'],
        'currency1' => 'USD',
        'currency2' => $obt['currency'],
        'status' => 100,
        'status_text' => 'Manual confirmation',
        'ipn_mode' => 'hmac',
        'merchant' => "",
        'custom' => $obt['id'],
        'admin' => $user['id']
    );


    $data = http_build_query($post);
    $hmac_signature = hash_hmac('sha512', $data, BITCOIN_HMAC_IPN_KEY);
    $header = array(
        'HMAC: ' . $hmac_signature,
        'Content-Type: application/x-www-form-urlencoded'
    );

    //request the payment
    $options = array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => ROOT_URL."finish_bitcoin_transaction.php",
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 1,
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_POSTFIELDS => $data
    );
    $ch = curl_init();
    curl_setopt_array($ch, $options);
    if (!$result = curl_exec($ch)) {
        trigger_error(curl_error($ch));
    }
    curl_close($ch);

    error_log($result);
    $result = json_decode($result);
    error_log(print_r($result, true));    

    if ($result->error === 'ok') {
        $result->message = "OK";
        $result->code = 1;
        $jsonOut=json_encode($result);		
        die($jsonOut);       
    }
        
    $result->message = $result->error;
    $result->code = -1;
    $jsonOut=json_encode($result);		
    die($jsonOut);   
?>