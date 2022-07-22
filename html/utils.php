<?php

include_once(__DIR__ . "/lib/protoncapitalmarkets.php");
include_once(__DIR__ . "/db.php");
include_once(__DIR__ . "/configuration.php");
include_once(__DIR__ . "/enums.php");

use Brokers\ProtonCapitalMarketsBroker;

function randomPassword()
{
	// $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	$alphabet = 'abcdefghijklmnopqrstuvwxyz1234567890';
	$pass = array(); //remember to declare $pass as an array
	$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	for ($i = 0; $i < 8; $i++) {
		$n = rand(0, $alphaLength);
		$pass[] = $alphabet[$n];
	}
	return implode($pass); //turn the array into a string
}

function changeUserPassword($userEmail, $newPassword, $userId)
{
	$pdo = getDatabase();

	$newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);

	$sql = "UPDATE `User` set `password` = :password WHERE id = :userid";
	$sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$sth->bindParam(':password', $newPasswordHash);
	$sth->bindParam(':userid', $userId);

	if (!$sth->execute()) {
		$msg = "Error: " . $sth->errorInfo()[2];
		error_log($msg);
		return false;
	} else {
		//update password for broker
		try {
		$broker = new ProtonCapitalMarketsBroker(PROTONCAPITALMARKETS_SERVERNAME, PROTONCAPITALMARKETS_AUTHCODE);
		$shaPasswordHash = hash("md5", $newPassword);
		$broker->updateClientPassword($shaPasswordHash, $userId);
		} catch (Exception $e)
		{
			error_log("BROKERPASSWORDCHANGE: ".$e->getMessage());
		}

		return true;
	}
}

function userClearMd5Hash($user_id)
{
	//disable for now
	return true;

    $pdo = getDatabase();

    $pdo->beginTransaction();

    $sql = "UPDATE User SET `md5_hash` = '' WHERE id = :id";
    $sth = $pdo->prepare($sql);

    $sth->bindParam(':id', $user_id);

    if (!$sth->execute()) {
        error_log("userClearMd5Hash failed!");
        return false;
    }

    $pdo->commit();

    return true;
}

function createHtmlTree($rootNode, $debug = false)
{
	$html = "";
	foreach ($rootNode["children"] as $licNode) {

		$html .= "<details>";

		$classes = "downLineSummary";
		if ($licNode["data"]["imported"])
			$classes .= " background-grey";

		$html .= "	<summary class=\"" . $classes . "\" data-userid=\"" . $licNode["data"]["id"] . "\">";

		$html .= $licNode["data"]["given_name"] . " " . $licNode["data"]["sur_name"] . " ";

		if (!$debug)
			$html .= "" . "</summary>";
		else
			$html .= $licNode["downline_direct_calculated"] . " " .
				$licNode["downline_all_calculated"] . "</summary>";

		$html .= "	<div class=\"downLineInline\">";
		$html .= "		<div class=\"downLineSpc\">";
		$html .= "		</div>";

		if ($licNode["depth"] > 0) {
			$html .= "			<div class=\"downLineContainer\">";
			if ($licNode["depth"] <= 1 || true) { //temporary allow full info on all nodes (remove "|| true" when not needed anymore)
				$html .= "				<div class=\"showDLRow\">";
				$html .= "					<div class=\"showDLFirstColumn\">";
				$html .= "						" . "Name";
				$html .= "					</div>";
				$html .= "					<div>";
				$html .= "					" . $licNode["data"]["given_name"] . " " . $licNode["data"]["sur_name"];
				$html .= "					</div>";
				$html .= "				</div>";

				$html .= "				<div class=\"showDLRow\">";
				$html .= "					<div class=\"showDLFirstColumn\">";
				$html .= "						" . "Email";
				$html .= "					</div>";
				$html .= "					<div>";
				$html .= "					" . $licNode["data"]["email"];
				$html .= "					</div>";
				$html .= "				</div>";
			}

			/*
			$html .= "				<div class=\"showDLRow\">";
			$html .= "					<div class=\"showDLFirstColumn\">";
			$html .= "						"."Level";
			$html .= "					</div>";
			$html .= "					<div>";
			$html .= "						".$licNode["depth"];
			$html .= "					</div>";
			$html .= "			    </div>";
			*/
			$html .= "				<div class=\"showDLChildren\"><img class=\"ic-loading\" src=\"./Images/loading.gif\"/ alt=\"loading\"></div>";
		}

		$html .= "	</div>";
		$html .= "</details>";
	}

	return $html;
}

function getTradingAccountID($tradingAccountName) {
	if ($tradingAccountName == ACCOUNT_1_NAME)
		return AccountType::BASIC;
	else if ($tradingAccountName == ACCOUNT_2_NAME)
		return AccountType::PLUS;
	else if ($tradingAccountName == ACCOUNT_3_NAME)
		return AccountType::PRO;
	else if ($tradingAccountName == ACCOUNT_4_NAME)
		return AccountType::PRO_PLUS;
	else
		return AccountType::UNKNOWN;
}

function getNameTradingAccount($iTradingAccount)
{
	if ($iTradingAccount == AccountType::BASIC)
		return ACCOUNT_1_NAME;
	else if ($iTradingAccount == AccountType::PLUS)
		return ACCOUNT_2_NAME;
	else if ($iTradingAccount == AccountType::PRO)
		return ACCOUNT_3_NAME;
	else if ($iTradingAccount == AccountType::PRO_PLUS)
		return ACCOUNT_4_NAME;
	else
		return "";
}

function getPaidAmountFromAccess($iTradingAccount) {
	if ($iTradingAccount == AccountType::BASIC)
		return ACCOUNT_1_FEE;
	else if ($iTradingAccount == AccountType::PLUS)
		return ACCOUNT_2_FEE;
	else if ($iTradingAccount == AccountType::PRO)
		return ACCOUNT_3_FEE;
	else if ($iTradingAccount == AccountType::PRO_PLUS)
		return ACCOUNT_4_FEE;
	else
		return -1;
}

function getOriginalPaidAmountFromAccess($iTradingAccount) {
	if ($iTradingAccount == AccountType::BASIC)
		return ACCOUNT_1_ORIGINAL_FEE;
	else if ($iTradingAccount == AccountType::PLUS)
		return ACCOUNT_2_ORIGINAL_FEE;
	else if ($iTradingAccount == AccountType::PRO)
		return ACCOUNT_3_ORIGINAL_FEE;
	else if ($iTradingAccount == AccountType::PRO_PLUS)
		return ACCOUNT_4_ORIGINAL_FEE;
	else
		return -1;
}

function getPerformanceFee($iTradingAccount) {
	if ($iTradingAccount == AccountType::BASIC)
		return "35";
	else if ($iTradingAccount == AccountType::PLUS)
		return "32";
	else if ($iTradingAccount == AccountType::PRO)
		return "28";
	else if ($iTradingAccount == AccountType::PRO_PLUS)
		return "25";
	else
		return "";
}

function getPaymentMethodName($paymentID, $lang)
{
	if ($paymentID == 1)
		return localize('reg6_payment_method_credit_card', $lang);
	else if ($paymentID == 2)
		return localize('reg6_payment_method_wire_transfer', $lang);
	else if ($paymentID == 3)
		return localize('reg6_payment_method_crypto', $lang);
	else if ($paymentID == 4)
		return localize('reg6_payment_method_banxa', $lang);
	else
		return localize('reg6_payment_method_error', $lang);
}

function createDownlineHtmlTableRows($nodeDownline, $nodeDownlineLevelRoot=0, $hide_surnames = false)
{
	$htmlTableRows = "";
	foreach ($nodeDownline["children"] as $nodeChild) {
		$add_class = '';
		if ($nodeChild["data"]["broker_registration_complete"] == 0)
		{
			$add_class = 'pending';
		}
		$htmlTableRows .= "<tr class=\"row_table_downline $add_class\" open=\"false\" data-num-direct=\"".$nodeChild["data"]["downline_direct_count"]."\" ";
		$htmlTableRows .= "data-user-id=\"".$nodeChild["data"]["id"]."\" ";
		$htmlTableRows .= "data-level=\"".($nodeChild["data"]["downline_level"]-$nodeDownlineLevelRoot)."\" ";
		$htmlTableRows .= ">";
		$htmlTableRows .= "<td class=\"open-control\">";

		if($nodeChild["data"]["downline_direct_count"] > 0)
			$htmlTableRows .= "<i class=\"fas fa-chevron-right\"></i>";
		$htmlTableRows .= "</td>";
		$htmlTableRows .= "<td>";
		$htmlTableRows .= ($nodeChild["data"]["downline_level"]-$nodeDownlineLevelRoot);
		$htmlTableRows .= "</td>";
		$htmlTableRows .= "<td>";
		$surname = $nodeChild["data"]["sur_name"];
		if (!empty($surname) && $hide_surnames)
		{
			$surname = substr($surname, 0, 1) . ".";
		}
		$htmlTableRows .= $nodeChild["data"]["given_name"]." ".$surname;
		$htmlTableRows .= "</td>";
		$htmlTableRows .= "<td>";
		$htmlTableRows .= $nodeChild["data"]["downline_direct_count"];
		$htmlTableRows .= "</td>";
		$htmlTableRows .= "<td>";
		$htmlTableRows .= $nodeChild["data"]["downline_total_count"];
		$htmlTableRows .= "</td>";
		$htmlTableRows .= "<td>";
		$htmlTableRows .= "$ ".number_format($nodeChild["data"]["access_downline_total"], 0, ",", ".");
		$htmlTableRows .= "</td>";
		$htmlTableRows .= "</tr>";
	}

	return $htmlTableRows;
}

function sendEmail($from, $to, $subject, $body, $attachments = array())
{
	$pdo = getDatabase();

	$attachments_json = json_encode($attachments);

	$sql = "INSERT INTO Emails (`from`, `to`, `subject`, `message`, `send_on`, `attachments`)" .
	"VALUES (:from, HEX(AES_ENCRYPT(:to, UNHEX('" . KEY_DB . "'))), " .	":subject, " .
	"HEX(AES_ENCRYPT(:message, UNHEX('" . KEY_DB . "'))), NOW(), :attachments)";
	$sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$sth->bindParam(':from', $from);
	$sth->bindParam(':to', $to);
	$sth->bindParam(':subject', $subject);
	$sth->bindParam(':message', $body);
	$sth->bindParam(':attachments', $attachments_json);

	if ($sth->execute()) {
		return $pdo->lastInsertId();
	}

	return false;
}

    //get emails to send
function getPendingEmails() {
	$pdo = getDatabase();

	$sql = "SELECT " .
		"id, " .
		"`from`, " .
		"AES_DECRYPT(UNHEX(`to`), UNHEX('" . KEY_DB . "')) AS `to`, " .
		"subject, " .
		"AES_DECRYPT(UNHEX(`message`), UNHEX('" . KEY_DB . "')) AS `message`, " .
		"attachments " .
		"FROM Emails " .
		"WHERE ((send_on IS NULL) OR (send_on < NOW())) AND sent_on IS NULL;";

	$sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	if ($sth->execute()) {
		return $sth->fetchAll();
	}

	return false;

}

function setMailSent($mailid) {

	$pdo = getDatabase();

	$sql = "UPDATE Emails SET sent_on=NOW() WHERE id=:mailid";
	$sth = $pdo->prepare($sql);
	$sth->bindParam(':mailid', $mailid);

	return $sth->execute();
}

function getPost($key)
{
    if (array_key_exists($key, $_POST))
    {
        return $_POST[$key];
    }

    return false;
}

function getRequest($key)
{
    if (array_key_exists($key, $_REQUEST))
    {
        return $_REQUEST[$key];
    }

    return false;
}

function getServer($key)
{
    if (array_key_exists($key, $_SERVER))
    {
        return $_SERVER[$key];
    }

    return false;
}

function getValueForProtonuserUpdate(&$arguments, &$User, $key, &$updateUser) {
	if(array_key_exists($key, $arguments)) {
		$updateUser = true;
		return $arguments[$key];
	} else {
		return $User[$key];
	}
}

function updateUser($pdo, $id, $arguments = [], $update_broker = true) {

	$params = [];
	$update = [];
	foreach ($arguments as $key => $value) {
		$params[":" . $key] = $value;
		$update[] = $key . ' = ' . ':' . $key;
	}

	try {
		$sql = "UPDATE User SET " . implode(", ", $update) . " WHERE id=:id";
		$sth = $pdo->prepare($sql);
		$params[":id"] = $id;
		$sth->execute($params);
	} catch (Exception $ex) {
		return $ex->getMessage();
	}

	$user = getUserByID($pdo, $id);
	$updateProtonuser = false;

	if(!empty($user) && $user['broker_registration_complete'] == 1 && $update_broker) {
		$name = getValueForProtonuserUpdate($arguments, $user, "given_name", $updateProtonuser);
		$surname = getValueForProtonuserUpdate($arguments, $user, "sur_name", $updateProtonuser);
		$dateofbirth = getValueForProtonuserUpdate($arguments, $user, "date_of_birth", $updateProtonuser);
		$housenumber = getValueForProtonuserUpdate($arguments, $user, "housenumber", $updateProtonuser);
		$address = getValueForProtonuserUpdate($arguments, $user, "street", $updateProtonuser);
		$city = getValueForProtonuserUpdate($arguments, $user, "city", $updateProtonuser);
		$postalcode = getValueForProtonuserUpdate($arguments, $user, "postcode", $updateProtonuser);

		$country_2_letter_iso = getValueForProtonuserUpdate($arguments, $user, "country", $updateProtonuser);
		$country_2_letter_iso = strtolower($country_2_letter_iso);

		$nationality_2_letter_iso = getValueForProtonuserUpdate($arguments, $user, "country", $updateProtonuser);
		$nationality_2_letter_iso = strtolower($nationality_2_letter_iso);

		$phone = getValueForProtonuserUpdate($arguments, $user, "mobile_number", $updateProtonuser);
		$phonecode = getValueForProtonuserUpdate($arguments, $user, "phonecode", $updateProtonuser);		// Phonecode from user

		$email = getValueForProtonuserUpdate($arguments, $user, "email", $updateProtonuser);

		$yourclientid = $user['id'];
		$acctype = getValueForProtonuserUpdate($arguments, $user, "trading_account", $updateProtonuser);
		if ($acctype == AccountType::BASIC) { $acctype = ProtonCapitalMarketsBroker::ACCTYPE_BASIC; }
		if ($acctype == AccountType::PLUS) { $acctype = ProtonCapitalMarketsBroker::ACCTYPE_PLUS; }
		if ($acctype == AccountType::PRO) { $acctype = ProtonCapitalMarketsBroker::ACCTYPE_PRO; }
		if ($acctype == AccountType::PRO_PLUS) { $acctype = ProtonCapitalMarketsBroker::ACCTYPE_PROPLUS; }

		//$query = "$name|$surname|$dateofbirth|$housenumber|$address|$city|$postalcode|$country_2_letter_iso|$nationality_2_letter_iso|$country[phonecode]|$phone|$yourclientid|$acctype";
		//error_log($query);

		if($updateProtonuser) {
			$country = getCountryByIso($pdo, $phonecode, $user['language']);
			try {
				$broker = new ProtonCapitalMarketsBroker(PROTONCAPITALMARKETS_SERVERNAME, PROTONCAPITALMARKETS_AUTHCODE);
				$broker->updateClientInfo($name, $surname, $dateofbirth, $housenumber,$address, $city, $postalcode, $country_2_letter_iso, $nationality_2_letter_iso, $country['phonecode'], $phone, $yourclientid, $acctype, $email);
			} catch (Exception $ex) {
				return $ex->getMessage();
			}
		}
	}
	return $user;
}

function getUserByEmail($pdo, $email, $userid) {

	$sql = "SELECT id FROM User WHERE id != :id AND email = :email";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([
		':id' => $userid,
		':email' => $email
	]);

	return $stmt->fetchAll();
}

function getUserByID($pdo, $id) {
	$sql = "SELECT * FROM User WHERE id = :id";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([
		':id' => $id
	]);

	return $stmt->fetch();
}

function AutomationChanged($Type, $TypeWaiting) {
	if($Type == AutomationType::OFF && $TypeWaiting == AutomationType::WAITING_FOR_ACTIVE) { return false; }
	if($Type == AutomationType::ON && $TypeWaiting == AutomationType::WAITING_FOR_INACTIVE) { return false; }

	return true;
}

function getSexFromUser($sex, $lang) {
	if($sex == 0) {
		return localize('reg1_sex_male', $lang);
	}
	return localize('reg1_sex_female', $lang);
}

// Returns a file size limit in bytes based on the PHP upload_max_filesize
// and post_max_size
function file_upload_max_size() {
	static $max_size = -1;

	if ($max_size < 0) {
		// Start with post_max_size.
		$post_max_size = parse_size(ini_get('post_max_size'));
		if ($post_max_size > 0) {
		$max_size = $post_max_size;
		}

		// If upload_max_size is less, then reduce. Except if upload_max_size is
		// zero, which indicates no limit.
		$upload_max = parse_size(ini_get('upload_max_filesize'));
		if ($upload_max > 0 && $upload_max < $max_size) {
		$max_size = $upload_max;
		}
	}
	return $max_size;
}

function parse_size($size) {
	$unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
	$size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
	if ($unit) {
		// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
		return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
	}
	else {
		return round($size);
	}
}

function getLanguageReadable($iso) {
	$iso = strtolower($iso);
	if($iso == "de") {
		return "Deutsch";
	} else if($iso == "en") {
		return "English";
	}
	else {
		return "Unknown language";
	}
}

function redirect($url){
    if (headers_sent()){
      die('<script type="text/javascript">window.location=\''.$url.'\';</script‌​>');
    }else{
      header('Location: ' . $url);
      die();
    }
}

function getOpenOrders($userID) {
	$pdo = getDatabase();

	$param = [
		':userId' => $userID
	];

	$htmlRowTemplate = file_get_contents(__DIR__ . "/html_templates/proof/OpenOrdersRowRemplate.html");

	$sql = "SELECT *, DATE_FORMAT(DATE(OpenTime) , \"%d.%c.%Y\")as readableDate, TIME(OpenTime) as readableTime FROM OpenOrders WHERE userId = 1 OR userId = :userId";

	$completeHTML = "";

	$stmt = $pdo->prepare($sql);
	if($stmt->execute($param) && $htmlRowTemplate) {
		$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ($entries as $value) {
			$newRow = $htmlRowTemplate;
			$newRow = str_replace(":currentDate", $value['readableDate'], $newRow);
			$newRow = str_replace(":currentTime", $value['readableTime'], $newRow);
			$newRow = str_replace(":type", OpenOrdersCommandTypes::toString($value['Cmd']), $newRow);
			$newRow = str_replace(":symbol", $value['Symbol'], $newRow);
			$newRow = str_replace(":openPrice", $value['OpenPrice'], $newRow);
			$newRow = str_replace(":closedPrice", $value['CP'], $newRow);

			// TODO: calculate pip
			$pip = 100;
			$newRow = str_replace(":pip_value", number_format($pip, 2), $newRow);
			if($pip < 0) {
				$newRow = str_replace(":pip_classes", "banked_profits_losing", $newRow);
			} else {
				$newRow = str_replace(":pip_classes", "banked_profits_winning", $newRow);
			}
			$completeHTML .= $newRow;
		}
	}

	return $completeHTML;
}

function _calculateProfit($key, &$response, $profitArray) {
	$finalProfit = [];
	$finalProfit['losing'] = 0;
	$finalProfit['winning'] = 0;

	foreach ($profitArray as $entry) {
		if($entry[$key] < 0) {
			$finalProfit['losing'] += 1;
		} else if($entry[$key] > 0) {
			$finalProfit['winning'] += 1;
		}
	}

	$response[$key] = $finalProfit;
}

function getBankedProfits($userID) {
	$param = [
		':userID' => $userID
	];

	$pdo = getDatabase();
	$response = [];

	// Monthly profits
	$sql = "SELECT SUM(u.profit) as monthly_profit, MONTH(u.open_time), YEAR(u.open_time) FROM `OrderHistory` as u WHERE userID = :userID AND (`comment` LIKE '%TradeBot%' OR `comment` LIKE '%ProconBot%') GROUP BY MONTH(u.open_time), YEAR(u.open_time)";
	$stmt = $pdo->prepare($sql);
	if($stmt->execute($param)) {
		_calculateProfit('monthly_profit', $response, $stmt->fetchAll(PDO::FETCH_ASSOC));
	}

	// Daily profits
	$sql = "SELECT SUM(u.profit) as daily_profit, MONTH(u.open_time), YEAR(u.open_time),DAY(u.open_time) FROM `OrderHistory` as u WHERE userID = :userID AND (`comment` LIKE '%TradeBot%' OR `comment` LIKE '%ProconBot%') GROUP BY DAY(u.open_time), MONTH(u.open_time), YEAR(u.open_time)";
	$stmt = $pdo->prepare($sql);
	if($stmt->execute($param)) {
		_calculateProfit('daily_profit', $response, $stmt->fetchAll(PDO::FETCH_ASSOC));
	}

	// Weekly profits
	$sql = "SELECT SUM(u.profit) as weekly_profit, YEARWEEK(u.open_time) FROM `OrderHistory` as u WHERE userID = :userID AND (`comment` LIKE '%TradeBot%' OR `comment` LIKE '%ProconBot%') GROUP BY YEARWEEK(u.open_time)";
	$stmt = $pdo->prepare($sql);
	if($stmt->execute($param)) {
		_calculateProfit('weekly_profit', $response, $stmt->fetchAll(PDO::FETCH_ASSOC));
	}

	// Closed Trades profit
	$sql = "SELECT `profit` as closedOrdersProfit FROM `OrderHistory` WHERE `userId` = :userID";
	$stmt = $pdo->prepare($sql);
	if($stmt->execute($param)) {
		_calculateProfit('closedOrdersProfit', $response, $stmt->fetchAll(PDO::FETCH_ASSOC));
	}

	return $response;
}


function getProfitHistory($userid, $lang) {

	$date = new DateTime();
	$year = intval($date->format('Y'));
	$month = intval($date->format('m'));


	$history = _getProfitHistory($userid, $lang, $month, $year);
	$months = getAllMonths($lang);

	$data = [];
	$labels = [];
	$i = 0;
	$historyIndex = 0;

	$monthToExpect = $month;

	while($i < 12) {
		$value['data'] = 0;
		$value['labels'] = strtoupper(substr($months[$monthToExpect - 1]['translated_name'], 0, 1));

		if(array_key_exists($historyIndex, $history)) {
			$entry = $history[$historyIndex];

			$monthInEntry = $entry['month'];
			if($monthInEntry == $monthToExpect) {
				$value['data'] = round($entry['profit'], 2);
				$value['labels'] = $entry['index_name'];
				$historyIndex++;
			}
		}

		$monthToExpect--;
		if($monthToExpect <= 0) {
			$monthToExpect = 12;
		}

		$data[] = $value['data'];
		$labels[] = $value['labels'];
		$i++;
	}

	$history['data'] = $data;
	$history['labels'] = $labels;

	return $history;

}

function isPreviousMonth($currentMonth, $oldMonth) {
	// December <- January
	if($currentMonth == 12 && $oldMonth == 1) {
		return true;
	}

	// Any other combination
	if($currentMonth + 1 == $oldMonth) {
		return true;
	}

	return false;
}

function getNewMonth($months, $currentMonth) {
	foreach ($months as $key => $value) {
		if($value['id'] == $currentMonth) {
			return $value;
		}
	}
	return null;
}

function getMonthFromDBString($dbstring) {
	$date = new DateTime($dbstring);
	return intval($date->format("n"));
}

/**
*   Returns the String-Representation for the start of given Month / Year
*   e.g.: Month = 1, year = 2020 -> returns 2020-01-01 00:00:00
*   useful for Select-Statements
*   @return string
*/
function createDateBeginOfMonthYear($month, $year)
{
	$date = new DateTime();
	$date->setDate($year, $month, 1);
	$date->setTime(0, 0, 0);

	return $date->format('Y-m-d H:i:s');
}

/**
*
*   Returns the String-Representation for the End of the given Month / Year
*   e.g.: Month = 1, year = 2020 -> returns 2020-01-31 23:59:59
*   useful for Select-Statements
*   @return string
*/
function createDateEndOfMonthYear($month, $year)
{
	$daysAmount = cal_days_in_month(CAL_GREGORIAN, $month, $year);

	$date = new DateTime();
	$date->setDate($year, $month, $daysAmount);
	$date->setTime(23, 59, 59);

	return $date->format('Y-m-d H:i:s');
}

function createPhoneCodeHtmlSelect($activeLang = '', $additionalClasses = "", $activeCode = '') {

	$countries = getAllCountries(getDatabase(), $activeLang);

	$select = "<select class=\"$additionalClasses\" id=\"phoneCodeSelect\">";

	foreach ($countries as $country) {
		$name = isset($country['name'])?$country['name']:$country['nicename'];
		$select .= "<option ";
		if(strtoupper($activeCode) == strtoupper($country["iso"])) {
			$select .= "selected";
		}
		$select .= " value=\"". $country["iso"]. "\">". $name . " (+". $country['phonecode']. ")</option>";
	}

	$select .= "</select>";
	return $select;
}


function getLocale($iso_code = 'en') {
	static $locales;
	if($locales === null) {
		$locales = json_decode(file_get_contents("locales.json"), true);
	}

	if(array_key_exists($iso_code, $locales)) {
		$locales_temp = $locales[$iso_code];
		if($iso_code != $locales['default']) {
			$locales_temp = array_merge($locales_temp, $locales[$locales['default']]);
		}

		return $locales_temp;
	}
	return $locales[$locales['default']];
}


?>
