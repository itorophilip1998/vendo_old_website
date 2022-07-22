<?php

require_once('configuration.php');
require_once('bitcoin.php');
require_once(__DIR__  . "/lib/protoncapitalmarkets.php");
require_once("db.php");
require_once("utils.php");
require_once("translate.php");
require_once(__DIR__  . "/lib/commissions.php");

use Brokers\ProtonCapitalMarketsBroker;
use Brokers\ProtonCapitalMarketsException;



$txn_id = getRequest('txn_id');
$item_name = getPost('item_name');
$item_number = getPost('item_number');
$amount1 = getPost('amount1');
$amount2 = getRequest('amount2');
$currency1 = getPost('currency1');
$currency2 = getPost('currency2');
$status = getPost('status');
$status_text = getPost('status_text');
$ipn_mode = getPost('ipn_mode');
$merchant = getPost('merchant');
$custom = getRequest('custom');
if (!$custom)
{
    //for withdrawal, we save transaction id in the note field, because custom field is not available
    $custom = getRequest('note');
}
$admin_id = intval(getRequest('admin'));

$httphmac2 = getServer('HTTP_HMAC');
$httphmac3 = file_get_contents('php://input');
$transaction_ok = true;

//check if transaction is valid
if (empty($txn_id)) {
    error_log("empty txn_id");
    $transaction_ok = false;
}

if (!isset($ipn_mode) || $ipn_mode != 'hmac') {
    error_log("empty hmac1");
    $transaction_ok = false;
}

if (!isset($httphmac2) || empty($httphmac2)) {
    error_log("empty hmac2");
    $transaction_ok = false;
}

if ($httphmac3 === FALSE || empty($httphmac3)) {
    error_log("empty hmac3");
    $transaction_ok = false;
}

$cp_merchant_id = '';
$cp_ipn_secret = BITCOIN_HMAC_IPN_KEY;

$hmac = hash_hmac("sha512", $httphmac3, trim($cp_ipn_secret));
if (!hash_equals($hmac, $httphmac2)) {
    error_log('HMAC Invalid');
    error_log('HMAC (Calculated): '.$hmac);
    error_log('HMAC (Expected): '.$httphmac2);
    error_log('HMAC (Payload):'.$httphmac3);
    $transaction_ok = false;
}

$order_currency = 'USD';
if ($currency1 != $order_currency) {
    error_log('Original currency mismatch! ' . $currency1 . ' != '.$order_currency);
    $transaction_ok = false;
}

$status = intval($status);
if ($status >= 100 || $status == 2) {
    // payment is complete or queued for nightly payout, success 
    $transaction_ok = true;
} else if ($status < 0) {
    //payment error, this is usually final but payments will sometimes be reopened if there was no exchange rate conversion or with seller consent
    error_log('payment error:' . $status_text);
    $transaction_ok = false;
} else {
    //payment is pending, you can optionally add a note to the order page
    error_log('payment pending' . $status_text);
    $transaction_ok = false;
}

if ($transaction_ok)
{
    $data = Bitcoin::finishBitcoinTransaction($txn_id, $amount1, $amount2, $custom, $admin_id);

    if ($data)
    {
        if ($data['status'] == 'complete' && $data['status'] != $data['prev_status']) //prevent double call
        {
            //complete registration
            $userid = $data['user_id'];

            $pdo = getDatabase();
            $user = readUser($pdo, $userid);
            $country = getCountryByIso($pdo, $user['country'], $user['language']);
            $countryPhonecode = getCountryByIso($pdo, $user['phonecode'], $user['language']);

			
			//API CHANGED: register needs acctype (AP-ID:16920)
			//map our trading_account to acctype (see ProtonCapitalMarketsBroker)
			$acctype = 0;
			if ($user['trading_account'] == 1) { $acctype = ProtonCapitalMarketsBroker::ACCTYPE_BASIC; }
			if ($user['trading_account'] == 2) { $acctype = ProtonCapitalMarketsBroker::ACCTYPE_PLUS; }
			if ($user['trading_account'] == 3) { $acctype = ProtonCapitalMarketsBroker::ACCTYPE_PRO; }
			if ($user['trading_account'] == 4) { $acctype = ProtonCapitalMarketsBroker::ACCTYPE_PROPLUS; }
            
            //register user at broker
            $broker = new ProtonCapitalMarketsBroker(PROTONCAPITALMARKETS_SERVERNAME, PROTONCAPITALMARKETS_AUTHCODE);
            $commissions_calculator = new Commissions;
            
            if($user['broker_registration_complete'] != 1) {
                // with "- REGISTRATION_FEE" because user is not yet registered - but has payed the fee - dont add the fee itself to the access_volume
                addAccessVolumeAndPropagateInUpline($pdo, $userid, $data['paid_amount_usd'] - REGISTRATION_FEE, $data['amount'] - REGISTRATION_FEE);

                $register_result = false;
                try {
                    $register_result = $broker->register($user['given_name'], $user['sur_name'], $user['email'], $countryPhonecode['phonecode'], $user['mobile_number'], $user['md5_hash'],  $user['date_of_birth'],$user['housenumber'], $user['street'], $user['city'], $user['postcode'], $country['id'], $country['id'], $userid, $acctype);                
                } catch (ProtonCapitalMarketsException $e) {
                    error_log($e->getMessage());
                }		
                
                if ($register_result == 'register_ok')
                {
                    $pdo = getDatabase();
                    updateUser($pdo, $userid, array('broker_registration_complete' => '1', 'affiliate_level' => 1, 'max_affiliate_level' => 1));
                }
            } else {
                // without "REGISTRATION_FEE" because user is already registered - and has upgraded to another package - and does not pay any fee for upgrading
                addAccessVolumeAndPropagateInUpline($pdo, $userid, $data['paid_amount_usd'], $data['amount'], false);

                $newTradingAccount = getTradingAccountID($data['product']);
                if($newTradingAccount != AccountType::UNKNOWN && $newTradingAccount > $user['trading_account']) {
                    $newUser = updateUser($pdo, $userid, [
                        'trading_account' => $newTradingAccount
                    ]);
                    if(is_array($newUser)) {
                        $user = $newUser;
                    }                  
                }

            }

            //separate try-block, because we want to try generating login token even if register has failed, in case we are repeating the process
            try {
                $broker_login_token = $broker->generateLoginToken($user['email'], $user['md5_hash'], $user['id']);
			} catch (ProtonCapitalMarketsException $e) {
				error_log($e->getMessage());
            }	

            $broker_autologin_link = "https://www.protoncapitalmarkets.com/loginwl.php?token=".$broker_login_token;
            $trading_account_type = getNameTradingAccount($user['trading_account']);

            //send email - congratulations, welcome to the club
            $language = $user['language'];
            $template_file = "email-templates/payment_complete_email_$language.html";
            if (!file_exists($template_file))
            {
                $template_file = "email-templates/payment_complete_email_en.html";
            }
    
            $subject = localize('payment_complete_email_subject', $language);
            $body = file_get_contents($template_file);
    
            $salutation_male = localize('salutation_male', $language);
            $salutation_female = localize('salutation_female', $language);
            $salutation = $user['sex'] ? $salutation_female : $salutation_male;

            $search = array("[[Salutation]]", "[[GivenName]]", "[[BrokerLink]]", "[[AccountType]]");
            $replace = array($salutation, $user['given_name'], $broker_autologin_link, $trading_account_type);

            $body = str_replace($search, $replace, $body);

            //images
            $attachments["logo1.png"] = ["path" => "Images/logo1.png", "disposition" => "inline", "cid" => "logo1.png"];
            $attachments["dashboard_preview.png"] = ["path" => "Images/dashboard_preview.png", "disposition" => "inline", "cid" => "dashboard_preview.png"];
            $attachments["broker_button.png"] = ["path" => "Images/broker_button.png", "disposition" => "inline", "cid" => "broker_button.png"];
    
            sendEmail(FROM_MAIL_GENERAL, $user['email'], $subject, $body, $attachments);

            $result_json = json_encode(array("error" => "ok"));
            die($result_json);
        }
        else if ($data['status'] == 'partial')
        {
            //send email - payment not complete - amount to low
        }
        else
        {
            //no message - our error or hacking attempt
        }
    }
}
?>