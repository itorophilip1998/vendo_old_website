<?php
    function returnError($message) {
        ob_end_clean();
        echo json_encode([
            "message" => $message,
            "code" => -3
        ]);
        die();
    }

    include("configuration.php");
    include("db.php");
    include("translate.php");
    include("utils.php");

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

    $countries = getAllCountries($pdo, $user['language']);

    $country = getCountryByIso($pdo, $user['country'], $user['language']);

    $loadAccountInformationForm = $_POST['loadAccountInformationForm'];
    $loadPersonalInformationForm = $_POST['loadPersonalInformationForm'];
    $loadPersonalAdressInfoForm = $_POST['loadPersonalAdressInfoForm'];
    $loadPasswordForm = $_POST['loadPasswordForm'];

    $loadAccountInformation = $_POST['loadAccountInformation'];
    $loadPersonalInformation = $_POST['loadPersonalInformation'];
    $loadPersonalAdress = $_POST['loadPersonalAdress'];

    $saveButtonText = localize("bo_profile_save", $user['language']);
    $discardButtonText = localize("bo_profile_discard", $user['language']);
    $changePasswortText = localize('bo_profile_change_password', $user['language']);

    $profileGreeting = localize('bo_profile_greetings', $user['language']);
    $profileGreeting = str_replace(':Name', $user['given_name'], $profileGreeting);

    if(ob_start()) {
        if(isset($loadAccountInformationForm)) {
            include("./html_templates/profile/formAccountInformation.php");
        } else if(isset($loadPersonalInformationForm)) {
            include("./html_templates/profile/formPersonalInformation.php");
        } else if(isset($loadPersonalAdressInfoForm)) {
            include("./html_templates/profile/formPersonalAdress.php");
        } else if(isset($loadPasswordForm)) {
            include("./html_templates/profile/formChangePassword.php");
        } else if(isset($loadAccountInformation)) {

            $updateuser = $_POST['updateUser'];
            if(isset($updateuser) && ($updateuser == "true")) {
                $language = $_POST['language'];
                //$email = $_POST['email'];
                $email = $user['email'];

                $anotherUser = getUserByEmail($pdo, $email, $user['id']);
                if(count($anotherUser) <= 0) {
                    $mobile_number = $_POST['mobile_number'];
                    $phonecode = $_POST['phonecode'];
                    $payoutAddress = $_POST['payout_address'];
    
                    if(isset($language) && isset($email) && isset($mobile_number) && isset($phonecode) && isset($payoutAddress)) {
                        $language = strtolower($language);
                        
                        $user = updateUser($pdo, $user['id'], [
                            'language' => strtolower($language),
                            'email' => $email,
                            'mobile_number' => $mobile_number,
                            'phonecode' => $phonecode,
                            'payout_address' => $payoutAddress
                        ]);
    
                        if(!is_array($user)) {
                            returnError($user);
                        }
                    }
                } else {
                    ob_end_clean();
                    echo json_encode([
                        'message' => localize('bo_profile_error_same_email', $user['language']),
                        'code' => -2
                    ]);
                    die();
                }
            }

            $countryPhoneCode = getCountryByIso($pdo, $user['phonecode'], $user['language']);

            include("./html_templates/profile/readonlyAccountInformation.php");
        } else if(isset($loadPersonalInformation)) {
            $updateuser = $_POST['updateUser'];
            if(isset($updateuser) && ($updateuser == "true")) {
                $given_name = $_POST['given_name'];
                $sur_name = $_POST['sur_name'];
                $sex = $_POST['sex'];
                $date_of_birth = $_POST['date_of_birth'];

                if(isset($given_name) && isset($sur_name) && isset($sex) && isset($date_of_birth)) {
                    $user = updateUser($pdo, $user['id'], [
                        'given_name' => $given_name,
                        'sur_name' => $sur_name,
                        'sex' => $sex,
                        'date_of_birth' => $date_of_birth
                    ]);

                    if(!is_array($user)) {
                        returnError($user);
                    }

                    $profileGreeting = localize('bo_profile_greetings', $user['language']);
                    $profileGreeting = str_replace(':Name', $user['given_name'], $profileGreeting);
                }
            }
        
            include("./html_templates/profile/readonlyPersonalInformation.php");
        } else if(isset($loadPersonalAdress)) {
            $updateuser = $_POST['updateUser'];
            if(isset($updateuser) && ($updateuser == "true")) {
                $housenumber = $_POST['housenumber'];
                $street = $_POST['street'];
                $postcode = $_POST['postcode'];
                $city = $_POST['city'];
                $countryTemp = $_POST['country'];

                if(isset($housenumber) && isset($street) && isset($postcode) && isset($city) && isset($countryTemp)) {
                    $user = updateUser($pdo, $user['id'], [
                        'country' => $countryTemp,
                        'street' => $street,
                        'housenumber' => $housenumber,
                        'postcode' => $postcode,
                        'city' => $city
                    ]);

                    if(!is_array($user)) {
                        returnError($user);
                    }

                    $country['translated_name'] = getCountryByIso($pdo, $countryTemp, $user['language'])['translated_name'];
                }
            }
        
            include("./html_templates/profile/readonlyPersonalAdress.php");
        } else {
            ob_end_clean();
            echo json_encode([
                "message" => "invalid method",
                "code" => -3
            ]);
            die();
        }
    }

    $data = ob_get_clean();
    ob_end_clean();

    echo json_encode([
        'data' => $data,
        'message' => "",
        'new_greeting' => $profileGreeting,
        'code' => 200
    ]);
?>