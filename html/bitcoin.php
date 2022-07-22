<?php

require_once("configuration.php");
require_once("db.php");
require_once("enums.php");

class BitCoin
{
	const callback_url = ROOT_URL . "finish_bitcoin_transaction.php";
	const api_url = BITCOIN_API_URL;

	protected static $db = null;

	/// <returns>
	/// if failed: false
	/// if success: object containing following elements:
	/// {
	/// 	"amount":"1.00000000",
	/// 	"address":"ZZZ",
	/// 	"txn_id":"XXX",
	/// 	"checkout_url":"https://www.coinpayments.net/index.php?cmd=checkout&id=XXX&key=ZZZ"
	/// 	"status_url":"https://www.coinpayments.net/index.php?cmd=status&id=XXX&key=ZZZ"
	/// 	"qrcode_url":"https://www.coinpayments.net/qrgen.php?id=XXX&key=ZZZ"
	///  }
	/// </returns>
	public static function startBitcoinPayment($userId, $product, $amount, $currency, $type, $reuse_existing_transaction = false)
	{
		return self::startBitcoinTransaction($userId, $product, $amount, $currency, $type, false, $reuse_existing_transaction);
	}

	public static function startBitcoinWithdrawal($userId, $amount, $withdraw_address)
	{

		//extract bitcoin wallet address
		if (!empty($withdraw_address)) {
			$withdraw_address = str_replace("bitcoin:", "", $withdraw_address);
		}

		return self::startBitcoinTransaction($userId, 'withdrawal', $amount, "USDT", PaymentType::WITHDRAWAL, $withdraw_address);
	}

	public static function finishBitcoinTransaction($transaction_id, $paid_amount_usd, $paid_amount_coin, $open_buying_transaction_id, $admin_id = 0)
	{
		$obt = self::getOpenBuyingTransaction($open_buying_transaction_id);

		if ($obt['transaction_id'] != $transaction_id)
		{
			return false;
		}

		if ($paid_amount_coin < 0)
		{
			return false;
		}

		$obt['prev_status'] = $obt['status'];
		$status = 'complete';

		//if sent amount is to low
		if ($paid_amount_coin < $obt['api_amount'] * BITCOIN_PAYMENT_TOLERANCE)
		{
			$status = 'partial';
		}

		self::updatePaidAmount($open_buying_transaction_id, $paid_amount_usd, $paid_amount_coin, $status, $admin_id);

		$obt['status'] = $status;
		$obt['paid_amount'] = $paid_amount_coin;
		$obt['paid_amount_usd'] = $paid_amount_usd;

		return $obt;
	}

	public static function getPaymentHtml($userId, $product, $amount, $currency, $type, $reuse_existing_transaction = false, $paymentMethod = PaymentMethod::CRYPTO)
	{

		$payment = self::startBitcoinPayment($userId, $product, $amount, $currency, $type, $reuse_existing_transaction);


		if ($payment === false)
		{
			return false;
		}

		$classTextBitcoin2 = "";
		$format = localize("payment_bitcoin_amount");

		if ($payment && $payment->txn_id && $payment->qrcode_url)
		{
			//get QR Code from coinpayments
			$qr_code_url = $payment->qrcode_url;
			$bc_address = $payment->address;
			$bc_amount = $payment->amount;
			$bitcoin_success = true;

			$textBitcoinAmount = str_replace(":amount:", number_format($bc_amount, 8, '.', ','), $format);
			$textBitcoinAmount = str_replace(":currency:", $currency, $textBitcoinAmount);
		}
		else{
			$classTextBitcoin2 = "red_bitcoin_failed";
			$textBitcoinAmount = localize('payment_bitcoin_payment_failed');
			$qr_code_url = BITCOIN_REQUEST_FAILED_IMAGE_URL;
			$bitcoin_success = false;
		}

		//placeholders
		// :classTextBitcoin2
		// :textBitcoinAmount
		// :hidden_if_fail //set to "hidden" if payment not successfull
		// :bc_address
		// :bc_amount
		// :qr_code_url
		if ($paymentMethod == PaymentMethod::BANXA)
		{
			$template = file_get_contents ('html_templates/banxa_payment.php');
		}
		else{
			$template = file_get_contents ('html_templates/bitcoin_payment.php');
		}

		$template = preg_replace_callback(
			"|:localize:(.*?):|",
			function($matches) {
				return localize($matches[1]);
			},
			$template
		);

		$replace_array = array(
			':bc_success' => ($bitcoin_success?'true':'false'),
			':classTextBitcoin2' => $classTextBitcoin2,
			':textBitcoinAmount' => $textBitcoinAmount,
			':hidden_if_fail' => ($bitcoin_success?'':'hidden'),
			':hidden_if_success' => ($bitcoin_success?'hidden':''),
			':bc_address' => $bc_address,
			':bc_amount' => floatval($bc_amount),
			':qr_code_url' => $qr_code_url
		);



		$final_html = strtr($template, $replace_array);

		return $final_html;
	}

	private static function startBitcoinTransaction($userId, $product, $amount, $currency, $type, $withdraw_address = false, $reuse_existing_transaction = false)
	{
		try {
			if ($reuse_existing_transaction)
			{
				$obt = self::getOpenBuyingTransactionForUser($userId);

				if ($obt)
				{
					$transaction = new stdClass();
					$transaction->amount = $obt['api_amount'];
					$transaction->address = $obt['address'];
					$transaction->txn_id = $obt['transaction_id'];
					$transaction->checkout_url = $obt['checkout_url'];
					$transaction->status_url = $obt['status_url'];
					$transaction->qrcode_url = $obt['qrcode_url'];

					return $transaction;
				}
				else if ($reuse_existing_transaction === 'force_reuse')
				{
					return false;
				}
			}

			//call API Function
			$open_buying_transaction_id = self::insertOpenBuyingTransaction($product, $userId, $amount, $currency, $type);

			$transaction = false;
			if ($withdraw_address)
			{
				$transaction = self::requestWithdrawal($open_buying_transaction_id, $withdraw_address);
			}
			else
			{
				$transaction = self::requestPayment($open_buying_transaction_id);
			}

			if (!$transaction) {
				//self::deleteOpenBuyingTransaction($open_buying_transaction_id);
				return false;
			}

			if ($withdraw_address)
			{
				$open_buying_transaction_id = self::updateWithdrawalTransaction($transaction, $withdraw_address, $open_buying_transaction_id);
			}
			else
			{
				$open_buying_transaction_id = self::updateOpenBuyingTransaction($transaction, $open_buying_transaction_id);
			}

			if ($open_buying_transaction_id) {
				return $transaction;
			} else {
				return false;
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
		return false;
	}

	private static function preparePaymentData($open_buying_transaction_id) {

		$obt = self::getOpenBuyingTransaction($open_buying_transaction_id);
		if (!$obt) {
			return false;
		}

		$currency = $obt['currency'];
		if (empty($currency))
		{
			$currency = "USDT.ERC20";
		}

		if(session_status() == PHP_SESSION_NONE){
			session_start();
		}

		$test_currency = isset($_SESSION['test_currency'])?$_SESSION['test_currency']:false;
		if ($test_currency === true)
		{
			$currency = 'LTCT';
		}

		$post = array(
			'version' => 1,
			'key' => BITCOIN_API_KEY,
			'cmd' => 'create_transaction',
			'amount' => $obt['amount'],
			'currency1' => 'USD',
			'currency2' => $currency,
			'buyer_email' => $obt['buyer_email'],
			'item_name' => $obt['product'],
			'item_number' => '',
			'custom' => $obt['id'],
			'ipn_url' => BitCoin::callback_url,
			'success_url' => "",
			'cancel_url' => ""
		);
		return $post;
	}

	private static function prepareWithdrawalData($open_buying_transaction_id, $withdraw_address) {

		$obt = self::getOpenBuyingTransaction($open_buying_transaction_id);
		if (!$obt) {
			return false;
		}

		$currency = $obt['currency'];
		if (empty($currency))
		{
			$currency = "USDT";
		}

		$post = array(
			'version' => 1,
			'key' => BITCOIN_API_KEY,
			'cmd' => 'create_withdrawal',
			'amount' => $obt['amount'],
			'currency' => $currency,
			'currency2' => 'USD',
			'note' => $open_buying_transaction_id,
			'ipn_url' => BitCoin::callback_url,
			'address' => $withdraw_address,
		);

		return $post;
	}

	private static function apiCall($post) {
		$data = http_build_query($post);
		$hmac_signature = hash_hmac('sha512', $data, BITCOIN_HMAC_KEY);
		$header = array(
			'HMAC: ' . $hmac_signature,
			'Content-Type: application/x-www-form-urlencoded'
		);

		//request the payment
		$options = array(
			CURLOPT_POST => 1,
			CURLOPT_HEADER => 0,
			CURLOPT_URL => self::api_url,
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

		if ($result->error === 'ok') {
			return $result->result;
		}

		return false;
	}

	private static function requestPayment($open_buying_transaction_id)
	{
		try {
			$post = self::preparePaymentData($open_buying_transaction_id);

			return self::apiCall($post);

		} catch (Exception $e) {
			error_log($e->getMessage());
			return false;
		}

		return false;
	}

	private static function requestWithdrawal($open_buying_transaction_id, $withdraw_address)
	{
		try {
			$post = self::prepareWithdrawalData($open_buying_transaction_id, $withdraw_address);

			return self::apiCall($post);

		} catch (Exception $e) {
			error_log($e->getMessage());
			return false;
		}

		return false;
	}

	protected static function insertOpenBuyingTransaction($product, $userId, $amount, $currency, $type)
	{
		$pdo = self::getDatabase();

		$pdo->beginTransaction();

		//insert new transaction
		$sql = "INSERT INTO OpenBuyingTransactions (product, user_id, amount, currency, status, started_on, type) VALUES (:product, :userId, :amount, :currency, 'pending', NOW(), :type)";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':product', $product);
        $sth->bindParam(':userId', $userId);
		$sth->bindParam(':amount', $amount);
		$sth->bindParam(':currency', $currency);
		$sth->bindParam(':type', $type);
        if (!$sth -> execute()) {
            $msg = "Error: ".$sth -> errorInfo()[2];
            error_log($msg);
			throw new Exception($sth -> errorInfo()[2], $sth -> errorInfo()[1]);
        }

		$id = $pdo->lastInsertId();
		$pdo->commit();

		return $id;
	}

	protected static function updatePaidAmount($open_buying_transaction_id, $paid_amount_usd, $paid_amount, $status, $admin_id = 0)
	{
		$pdo = self::getDatabase();

		$pdo->beginTransaction();

        $sql = 'UPDATE OpenBuyingTransactions SET '
            . '`paid_amount` = :paid_amount, `paid_amount_usd` = :paid_amount_usd, `status` = :status, `manual_confirmation` = :admin_id '
            . ' WHERE id = :id';
        $sth = $pdo->prepare($sql);

		$sth->bindParam(':paid_amount', $paid_amount);
		$sth->bindParam(':paid_amount_usd', $paid_amount_usd);
		$sth->bindParam(':status', $status);
		$sth->bindParam(':admin_id', $admin_id);

        $sth->bindParam(':id', $open_buying_transaction_id);

        if (!$sth->execute()) {
            error_log("Update OpenBuyingTransactions failed!");
            return false;
		}

		$pdo->commit();

		return $open_buying_transaction_id;
	}

	protected static function updateWithdrawalTransaction($transaction, $address, $open_buying_transaction_id)
	{
		$pdo = self::getDatabase();

        $sql = 'UPDATE OpenBuyingTransactions SET '
            . '`api_amount` = :api_amount, `address` = :address '
            . ' WHERE id = :id';
        $sth = $pdo->prepare($sql);

        $sth->bindParam(':api_amount', $transaction->amount);
        $sth->bindParam(':address', $address);

        $sth->bindParam(':id', $open_buying_transaction_id);

        if (!$sth->execute()) {
            error_log("Update OpenBuyingTransactions failed!");
            return false;
		}

		return $open_buying_transaction_id;
	}

    protected static function updateOpenBuyingTransaction($transaction, $open_buying_transaction_id)
    {
		$pdo = self::getDatabase();

        $sql = 'UPDATE OpenBuyingTransactions SET '
            . '`api_amount` = :api_amount, `address` = :address1, `dest_tag` = :dest_tag, '
            . '`transaction_id` = :transaction_id, `confirms_needed` = :confirms_needed, '
            . '`timeout` = :timeout1, `checkout_url` = :checkout_url, `status_url` = :status_url, '
            . '`qrcode_url` = :qrcode_url '
            . ' WHERE id = :id';
        $sth = $pdo->prepare($sql);

        $sth->bindParam(':api_amount', $transaction->amount);
        $sth->bindParam(':address1', $transaction->address);
        $sth->bindParam(':dest_tag', $transaction->dest_tag);
        $sth->bindParam(':transaction_id', $transaction->txn_id);
        $sth->bindParam(':confirms_needed', $transaction->confirms_needed);
        $sth->bindParam(':timeout1', $transaction->timeout);
        $sth->bindParam(':checkout_url', $transaction->checkout_url);
        $sth->bindParam(':status_url', $transaction->status_url);
        $sth->bindParam(':qrcode_url', $transaction->qrcode_url);

        $sth->bindParam(':id', $open_buying_transaction_id);

        if (!$sth->execute()) {
            error_log("Update OpenBuyingTransactions failed!");
            return false;
		}

		return $open_buying_transaction_id;
    }

	protected static function deleteOpenBuyingTransaction($open_buying_transaction_id)
	{
		$pdo = self::getDatabase();

		$pdo->beginTransaction();

		//insert new transaction
		$sql = "DELETE FROM OpenBuyingTransactions WHERE id = :id";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':id', $open_buying_transaction_id);
        if (!$sth -> execute()) {
            $msg = "Error: ".$sth -> errorInfo()[2];
            error_log($msg);
			throw new Exception($sth -> errorInfo()[2], $sth -> errorInfo()[1]);
        }

		$pdo->commit();
	}

	public static function getOpenBuyingTransaction($open_buying_transaction_id)
	{
		$pdo = self::getDatabase();

		//insert new transaction
		$sql = "SELECT * FROM OpenBuyingTransactions WHERE id = :id";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':id', $open_buying_transaction_id);
        if (!$sth -> execute()) {
            $msg = "Error: ".$sth -> errorInfo()[2];
            error_log($msg);
			throw new Exception($sth -> errorInfo()[2], $sth -> errorInfo()[1]);
        }

		$open_buying_transaction = $sth -> fetch(PDO:: FETCH_ASSOC);

        return $open_buying_transaction;
	}

	public static function getTotalOpenBuyingTransactionsSumInUsdForMonth($yearmonth)
	{
		$pdo = self::getDatabase();
		$year_month = $yearmonth."%";

		$sql = "SELECT COUNT(*) AS total, SUM(paid_amount_usd) AS totalsum FROM OpenBuyingTransactions WHERE product IN ('Basic', 'Plus', 'Pro', 'Pro+') AND status = 'complete' AND started_on LIKE :yearmonth";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':yearmonth', $year_month);
        if (!$sth -> execute()) {
            $msg = "Error: ".$sth -> errorInfo()[2];
            error_log($msg);
			throw new Exception($sth -> errorInfo()[2], $sth -> errorInfo()[1]);
        }

		$open_buying_transactions = $sth -> fetch(PDO:: FETCH_ASSOC);

        return $open_buying_transactions;
	}

	public static function getOpenBuyingTransactionForUser($user_id)
	{
		$pdo = self::getDatabase();

		$sql = "SELECT * FROM OpenBuyingTransactions WHERE user_id = :user_id AND status = 'pending'";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':user_id', $user_id);
        if (!$sth -> execute()) {
            $msg = "Error: ".$sth -> errorInfo()[2];
            error_log($msg);
			throw new Exception($sth -> errorInfo()[2], $sth -> errorInfo()[1]);
        }

		$open_buying_transaction = $sth -> fetch(PDO:: FETCH_ASSOC);

        return $open_buying_transaction;
	}

	//if $user_id is set, then only given user will be checked, otherwise, all users will be checked
	// return value is array cointaining information about expired pending payment(s)
	public static function checkPaymentTimeout($user_id = false)
	{
		$pdo = self::getDatabase();

		$where_user = "";
		$params = array();

		if (is_numeric($user_id))
		{
			$where_user = " AND user_id = :user_id";
			$params[':user_id'] = $user_id;
		}
		$sql = "SELECT * FROM OpenBuyingTransactions WHERE (status = 'pending' AND DATE_ADD(started_on, INTERVAL `timeout`*2 second) <= NOW())". $where_user;
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        if (!$sth -> execute($params)) {
            $msg = "Error: ".$sth -> errorInfo()[2];
            error_log($msg);
			throw new Exception($sth -> errorInfo()[2], $sth -> errorInfo()[1]);
        }

		$open_buying_transaction = $sth -> fetchAll(PDO:: FETCH_ASSOC);

        return $open_buying_transaction;
	}

	public static function getPendingPayment($user_id)
	{
		$obt = self::checkPaymentTimeout($user_id);
		if (!empty($obt)) //timeout occured
		{
			$obt = $obt[0];// take first result (probably the only one)
			$restart_payment_link = BITCOIN_RESTART_PAYMENT_URL . "?payment_id=".$obt['id'];
			return array('status' => 'timeout', 'link' => $restart_payment_link, 'payment_id' => $obt['id']);
		}
		else //still valid
		{
			$obt = self::getOpenBuyingTransactionForUser($user_id);
			if (is_array($obt))
			{
				$payment_status_link = $obt['status_url'];
				return array('status' => 'pending', 'link' => $payment_status_link, 'payment_id' => $obt['id']);
			}
		}

		//payment not found
		return array('status' => 'none');
	}

	public static function getPaymentInfo($id)
	{
		$pdo = self::getDatabase();

		$sql = "SELECT * FROM OpenBuyingTransactions WHERE id = :id";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':id', $id);
        if (!$sth -> execute()) {
            $msg = "Error: ".$sth -> errorInfo()[2];
            error_log($msg);
			throw new Exception($sth -> errorInfo()[2], $sth -> errorInfo()[1]);
        }

		$open_buying_transaction = $sth -> fetch(PDO:: FETCH_ASSOC);

        return $open_buying_transaction;
	}

	public static function updatePaymentStatus($payment_id, $status)
	{
		$pdo = self::getDatabase();

		$pdo->beginTransaction();

        $sql = 'UPDATE OpenBuyingTransactions SET status = :status WHERE id = :id';
        $sth = $pdo->prepare($sql);

        $sth->bindParam(':status', $status);
        $sth->bindParam(':id', $payment_id);

        if (!$sth->execute()) {
            error_log("updatePaymentStatus failed!");
            return false;
		}

		$pdo->commit();

		return $payment_id;
	}

	public static function getAllPendingTransactions()
	{
		$pdo = self::getDatabase();

		$sql = "SELECT CONCAT(u.`given_name`, ' ', `sur_name`) as user_name, obt.* FROM OpenBuyingTransactions obt JOIN User u ON obt.user_id = u.id WHERE obt.id in (select max(id) from OpenBuyingTransactions group by user_id) AND status != 'complete' ORDER BY obt.id DESC";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        if (!$sth -> execute()) {
            $msg = "Error: ".$sth -> errorInfo()[2];
            error_log($msg);
			throw new Exception($sth -> errorInfo()[2], $sth -> errorInfo()[1]);
        }

		$open_buying_transaction = $sth -> fetchAll(PDO:: FETCH_ASSOC);

        return $open_buying_transaction;
	}

	public static function getUncommissionedTransactions($daysago)
	{
		$pdo = self::getDatabase();

		$sql = "SELECT * FROM OpenBuyingTransactions WHERE status = 'complete' AND commissioned = 0 AND DATE_ADD(started_on, INTERVAL :daysago DAY) <= NOW() ORDER BY id ASC";
		$sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->bindParam(':daysago', $daysago);
        if (!$sth -> execute()) {
            $msg = "Error: ".$sth -> errorInfo()[2];
            error_log($msg);
			throw new Exception($sth -> errorInfo()[2], $sth -> errorInfo()[1]);
        }

		$open_buying_transaction = $sth -> fetchAll(PDO:: FETCH_ASSOC);

        return $open_buying_transaction;
	}

	public static function setTransactionCommissioned($payment_id)
	{
		$pdo = self::getDatabase();

		$pdo->beginTransaction();

        $sql = 'UPDATE OpenBuyingTransactions SET commissioned = 1 WHERE id = :id';
        $sth = $pdo->prepare($sql);

        $sth->bindParam(':id', $payment_id);

        if (!$sth->execute()) {
            error_log("setTransactionCommissioned failed!");
            return false;
		}

		$pdo->commit();

		return $payment_id;
	}

	protected static function getUser($user_id)
	{
		$pdo = self::getDatabase();

		return readUser($pdo, $user_id);
	}

	protected static function getDatabase()
	{
		if (!self::$db)
		{
			self::$db = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}

		return self::$db;
	}
}
