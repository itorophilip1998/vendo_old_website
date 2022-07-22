<?php

use Brokers\ProtonCapitalMarketsBroker;

try {
        include_once("./translate.php");
        include_once("./configuration.php");
        include_once("./db.php");
        include_once("./utils.php");
        include_once("./enums.php");
    
        session_start(); // start or continue a session
        $loggedInAsSponsorId = $_SESSION["loggedInAsUserId"];
        if (!$loggedInAsSponsorId) {
            $result["message"] = "Your session has expired, please log in again.";
            $result["code"] = -2;
            $jsonOut=json_encode($result);		
            die($jsonOut);            
        }
        
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); 
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        include './bo_read_user_logged_in.php';

        $language = $user['language'];

        $checked = $_POST['checked'];

        if(empty($_POST) || !isset($checked)) {
            die(json_encode([
                'msg' => localize('no_valid_fields', $language),
                'code' => 401
            ]));
        }

        $broker = new ProtonCapitalMarketsBroker(PROTONCAPITALMARKETS_SERVERNAME, PROTONCAPITALMARKETS_AUTHCODE);

        $InfoUser = $broker->getInfo($user['id']);
        $trading_account_type = getNameTradingAccount($user['trading_account']);
        $PerformanceFee = getPerformanceFee($user['trading_account']);

        $date = date("d.m.Y H:i:s");

        $body = "";
        $subject = "";

        $filename = "./email-templates/automation_email_changed_" . $user['language'] . ".html";
        if (!file_exists($filename))
        {
            $filename = "./email-templates/automation_email_changed_en.html";
        }

        
        $body = file_get_contents($filename);
        $feeText = file_get_contents("./email-templates/automation_email_changed_".$user['language']."_feetext.html");

        $pendingText = localize('bo_main_automation_change_pending_on', $user['language']);
        if($checked == "true") {
        
            $body = str_replace("[[Accounttype]]", $trading_account_type, $body); 
            $body = str_replace("[[CurrentStatus]]", "STOP", $body); 
            $body = str_replace("[[CurrentDate]]", $date, $body); 
            $body = str_replace("[[Accountnumber]]", $InfoUser['AccountNumber'], $body); 
            $body = str_replace("[[Fullname]]", $user['given_name'] . " " . $user['sur_name'], $body);
            $body = str_replace("[[Email]]", $user['email'], $body);
            $body = str_replace("[[state]]", localize('bo_change_automatic_deactivate', $user['language']), $body);
            $body = str_replace("[[oldStatus]]", localize('bo_change_automatic_deactivation', $user['language']), $body);
            
            $body = str_replace("[[FeeText]]", "", $body);

            $subject = localize('bo_automatic_send_mail_subject_end', $user['language']);

            $pendingText = localize('bo_main_automation_change_pending_off', $user['language']);
            updateUser($pdo, $user['id'], [
                "automation" => AutomationType::WAITING_FOR_INACTIVE
            ]);
                
        } else {
            
            $body = str_replace("[[Accounttype]]", $trading_account_type, $body); 
            $body = str_replace("[[CurrentStatus]]", "START", $body); 
            $body = str_replace("[[CurrentDate]]", $date, $body); 
            $body = str_replace("[[Accountnumber]]", $InfoUser['AccountNumber'], $body); 
            $body = str_replace("[[Fullname]]", $user['given_name'] . " " . $user['sur_name'], $body); 
            $body = str_replace("[[Email]]", $user['email'], $body); 
            $body = str_replace("[[state]]", localize('bo_change_automatic_activate', $user['language']), $body);
            $body = str_replace("[[oldStatus]]", localize('bo_change_automatic_activation', $user['language']), $body);
            $body = str_replace("[[FeeText]]", $feeText, $body);
            
            $subject = localize('bo_automatic_send_mail_subject', $user['language']);
            
            updateUser($pdo, $user['id'], [
                "automation" => AutomationType::WAITING_FOR_ACTIVE
            ]);
        }
        
        $body = str_replace("[[PerformanceFee]]", $PerformanceFee, $body);

        //images
        $attachments["agbs.pdf"] = ["path" => "./agbs/Proton_AGB_" . strtoupper($user['language']) . ".pdf", "disposition" => "attachment"];
        
        $sent = sendEmail(FROM_MAIL_GENERAL, "info@protoncapitalmarkets.com", $subject, $body, $attachments);
        $sent = sendEmail(FROM_MAIL_GENERAL, "service@protoncapitalmarkets.com", $subject, $body, $attachments);
        $sent = sendEmail(FROM_MAIL_GENERAL, $user['email'], $subject, $body, $attachments);

        echo json_encode([
            'data' => $pendingText,
            'msg' => localize('message_successfully_send', $language),
            'code' => 200
        ]);
    } catch(Exception $e) {
        error_log(var_dump($e));
        echo json_encode([
            'msg' => localize('message_failed_to_send', $language),
            'code' => 5001
        ]);
    }

?>