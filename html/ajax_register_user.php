<?php

    require_once(__DIR__  . "/lib/protoncapitalmarkets.php");
    use Brokers\ProtonCapitalMarketsBroker;

    $result=[];

    require_once("configuration.php");
    require_once("db.php");
    require_once("translate.php");
    require_once("utils.php");

    require_once(__DIR__  . "/lib/commissions.php");

    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	try {            
        //
        $temporaryCode = trim($_REQUEST["temporaryCode"]);
        $temporaryCodeCheckDateTime = trim($_REQUEST["temporaryCodeCheckDateTime"]);

        $language = trim($_REQUEST["language"]);

        $sex = trim($_REQUEST["sex"]);
        $givenName = trim($_REQUEST["givenName"]);
        $surName = trim($_REQUEST["surName"]);
        $dateOfBirthText = trim($_REQUEST["dateOfBirthText"]);

        $street = trim($_REQUEST["street"]);
        $housenumber = trim($_REQUEST["housenumber"]);
        $zip = trim($_REQUEST["zip"]);
        $city = trim($_REQUEST["city"]);
        $country = trim($_REQUEST["country"]);

        $mobileNumber = trim($_REQUEST["mobileNumber"]);
        $email = trim($_REQUEST["email"]);

        $countryCodeForPhoneCode = trim($_REQUEST['countryCodeForPhoneCode']);

        $password = trim($_REQUEST["password"]);
        
        $tradingAccount = trim($_REQUEST["tradingAccount"]);

        $paymentMethod = trim($_REQUEST["paymentMethod"]);

        $currency = trim($_REQUEST["currency"]);

        //
        $result["message"] = "";

        if(!$temporaryCode)
            $result["message"] .= "Parameter temporaryCode missing. ";
        if(!$temporaryCodeCheckDateTime)
            $result["message"] .= "Parameter temporaryCodeCheckDateTime missing. ";

        if(!$language)
            $result["message"] .= "Parameter language missing. ";

        if($sex != "0" && $sex != "1")
            $result["message"] .= "Parameter sex missing or faulty value. ";
        if($givenName != "" && !$givenName)
            $result["message"] .= "Parameter givenName missing. ";
        if(!$surName)
            $result["message"] .= "Parameter surName missing. ";
        if(!$dateOfBirthText)
            $result["message"] .= "Parameter dateOfBirthText missing. ";
        else {
            $timeBirth = new DateTime($dateOfBirthText);
            $age = $timeBirth->diff(new DateTime());
            $years = $age->format('%y');
            if($years < 18)
                $result["message"] .= "Parameter dateOfBirthText invalid. ";
        }

        if(!$street)
            $result["message"] .= "Parameter street missing. ";
        if(!$housenumber)
            $result["message"] .= "Parameter housenumber missing. ";        
        if(!$zip)
            $result["message"] .= "Parameter zip missing. ";
        if(!$city)
            $result["message"] .= "Parameter city missing. ";
        if(!$country)
            $result["message"] .= "Parameter country missing. ";
            
        if(!$countryCodeForPhoneCode) {
            $result["message"] .= "Parameter phonecode missing.";
        }

        if(!$mobileNumber)
            $result["message"] .= "Parameter mobileNumber missing. ";
        if(!$email)
            $result["message"] .= "Parameter email missing. ";
        else {
            $emailOk = filter_var($email, FILTER_VALIDATE_EMAIL);
            if(!$emailOk)
                $result["message"] .= "Parameter email is invalid. ";
        }

        if(!$password)
            $result["message"] .= "Parameter password missing. ";

        if(!$tradingAccount || $tradingAccount < 1 || $tradingAccount > 4)
            $result["message"] .= "Parameter tradingAccount missing or faulty value. ";

        if(!$paymentMethod || $paymentMethod < 1 || $paymentMethod > 4)
            $result["message"] .= "Parameter paymentMethod missing or faulty value. ";

        $result["message"] = trim($result["message"]);
        if($result["message"]) {
            $result["code"] = -1;
            $jsonOut=json_encode($result);		
		    die($jsonOut);
        }

        //
        $pdo->beginTransaction();

        $temporaryCodeCheckTime = strtotime($temporaryCodeCheckDateTime);
        $result = checkTemporaryCode($pdo, $temporaryCode, $temporaryCodeCheckTime);
        if($result["code"] < 0) {
            $pdo->rollback();
            die(json_encode($result));
        }
        
        $existingUser = readUserByEmail($pdo, $email);
        if($existingUser) {
            $pdo->rollback();
            $result["code"] = -10;
            $result["message"] = "Email '".$email."' already in use.";
            die(json_encode($result));
        }

        $parent = readUser($pdo, $result["data"]["sponsor_user_id"]);
        $level = $parent['downline_level'] + 1;

        $sql = "INSERT INTO User (".
            "upline_user_id, language, sex, given_name, sur_name, date_of_birth, ".
            "street, housenumber, postcode, city, country, phonecode, mobile_number, email, ".
            "`password`, `md5_hash`, trading_account, payment_method, date_of_entry, downline_level".
        ") VALUES (".
            ":uplineUserId, :language, :sex, :givenName, :surName, :dateOfBirth, ".
            ":street, :housenumber, :postcode, :city, :country, :phonecode, :mobileNumber, :email, ".
            ":password, :md5_hash, :tradingAccount, :paymentMethod, NOW(), :level".
        ")";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':uplineUserId', $result["data"]["sponsor_user_id"]);
        $sth->bindParam(':language', $language);
        $sth->bindParam(':sex', $sex);
        $sth->bindParam(':givenName', $givenName);
        $sth->bindParam(':surName', $surName);
        $sth->bindParam(':dateOfBirth', $dateOfBirthText);
        $sth->bindParam(':street', $street);
        $sth->bindParam(':housenumber', $housenumber);
        $sth->bindParam(':postcode', $zip);
        $sth->bindParam(':city', $city);
        $sth->bindParam(':country', $country);
        $sth->bindParam(':phonecode', $countryCodeForPhoneCode);
        $sth->bindParam(':mobileNumber', $mobileNumber);
        $sth->bindParam(':email', $email);
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $md5_hash = hash("md5", $password);
        $sth->bindParam(':password', $passwordHash);
        $sth->bindParam(':md5_hash', $md5_hash);
        $sth->bindParam(':tradingAccount', $tradingAccount);
        $sth->bindParam(':paymentMethod', $paymentMethod);
        $sth->bindParam(':level', $level);

        if (!$sth -> execute()) {
            $msg = "Error: ".$sth -> errorInfo()[2];
            error_log($msg);
            $result["code"] = -11;
            $result["message"] = $msg;
            $jsonOut=json_encode($result);
            $pdo->rollback();
            die($jsonOut);
        }
        $userId = $pdo->lastInsertId();
        $result["userId"] = $userId;

        //
        $sql = "UPDATE TemporaryEntryCodes SET user_id=:userId WHERE id=:entryCodeId";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':userId', $userId);
        $sth->bindParam(':entryCodeId', $result["data"]["id"]);
        if (!$sth -> execute()) {
            $msg = "Error: ".$sth -> errorInfo()[2];
            error_log($msg);
            $result["code"] = -12;
            $result["message"] = $msg;
            $jsonOut=json_encode($result);
            $pdo->rollback();
            die($jsonOut);
        }

        $pdo->commit();     

        // treat session as logged in
        session_start(); // start or continue a session
        $_SESSION["loggedInAsUserId"] = $userId;
        $_SESSION["locale"]=$language;

        $template_file = "email-templates/register_email_$language.html";
        if (!file_exists($template_file))
        {
            $template_file = "email-templates/register_email_en.html";
        }

        $subject = localize('register_email_subject', $language);
        $body = file_get_contents($template_file);

        $salutation_male = localize('salutation_male', $language);
        $salutation_female = localize('salutation_female', $language);
        $salutation = $sex ? $salutation_female : $salutation_male;
        $body = str_replace("[[Salutation]]", $salutation, $body);
        $body = str_replace("[[GivenName]]", $givenName, $body);
        $body = str_replace("[[DashboardLink]]", ROOT_URL."bo_main.php", $body);

        //images
        $attachments["logo1.png"] = ["path" => "Images/logo1.png", "disposition" => "inline", "cid" => "logo1.png"];
        $attachments["dashboard_preview.png"] = ["path" => "Images/dashboard_preview.png", "disposition" => "inline", "cid" => "dashboard_preview.png"];
        $attachments["dashboard_button.png"] = ["path" => "Images/dashboard_button.png", "disposition" => "inline", "cid" => "dashboard_button.png"];

        sendEmail(FROM_MAIL_GENERAL, $email, $subject, $body, $attachments);
        
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
?>