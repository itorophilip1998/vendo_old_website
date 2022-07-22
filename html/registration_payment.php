<?php
	require_once("translate.php");
	require_once("configuration.php");
	require_once("bitcoin.php");
	include_once("utils.php");
	include_once("enums.php");

	$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	//recalculate sum (prevent javascript hacking)
	$registrationFee = REGISTRATION_FEE;

	$tradingAccount = $_REQUEST['tradingAccount'];
	$paymentMethod = $_REQUEST['paymentMethod'];
	$currency = $_REQUEST['currency'];
	$accountName = "";
	$accountFee = 0;
	if ($tradingAccount == 1) {
		$accountName = ACCOUNT_1_NAME;
		$accountFee = ACCOUNT_1_FEE;
	} else if ($tradingAccount == 2) {
		$accountName = ACCOUNT_2_NAME;
		$accountFee = ACCOUNT_2_FEE;
	} else if ($tradingAccount == 3) {
		$accountName = ACCOUNT_3_NAME;
		$accountFee = ACCOUNT_3_FEE;
	} else if ($tradingAccount == 4) {
		$accountName = ACCOUNT_4_NAME;
		$accountFee = ACCOUNT_4_FEE;
	}
	else{
		//todo: error unknown account type -> return to trading account type selection
	}

	$total_amount = $accountFee;

	$type = PaymentType::ACCESS;

	$ajaxCall = $_POST['ajaxCall'];
	$oldtradingAccount = $_POST['oldtradingAccount'];
	if(isset($ajaxCall) && isset($oldtradingAccount)) {
		if($ajaxCall == "true") {
			session_start(); // start or continue a session
			$loggedInAsSponsorId = $_SESSION["loggedInAsUserId"];
			if (!$loggedInAsSponsorId) {
				$result["message"] = "Your session has expired, please log in again.";
				$result["code"] = -10;
				$jsonOut=json_encode($result);		
				die($jsonOut);            
			}
			$type = PaymentType::UPGRADE;
			$registrationFee = 0;
			$total_amount -= getOriginalPaidAmountFromAccess($oldtradingAccount);
		}
	}

	$total_amount += $registrationFee;

	$userId = $_REQUEST['userId'];

	//connect to coinpayments
	if ($paymentMethod == 4)
	{
		$currency = BANXA_CURRENCY;
	}		
	if (empty($currency))
	{
		$currency = "USDT.ERC20";
	}

	
	$payment_html = BitCoin::getPaymentHtml($userId, $accountName, $total_amount, $currency, $type, true, $paymentMethod);

	if(isset($ajaxCall)) {
		if($ajaxCall == "true") {
			$data = [
				'data' => $payment_html,
				'code' => 200
			];

			$data = json_encode($data);
			die($data);
		}
	}
	
?>

<html>

<head>
	<title>Vendo</title>

	<?php
	echo file_get_contents("head.html");
	?>
</head>

<body>
	<div class="reg_container_all">
		<div class="reg_container_logo">
			<div class="panel_logo_logo">
				<img src='Images/logo1.png' width=180 height=60></img>
			</div>
			<div class="panel_logo_text">
				<?php echo localize("reg_payment_logo_text"); ?>
			</div>
		</div>

		<div class="reg_container_content">
			<div class="panel_logo_logo_input">
				<img src='Images/logo1.png' width=180 height=60></img>
			</div>

			<div class="panel_reg_fill_top">
			</div>

			<?php echo $payment_html; ?>

			<div class="verticalSpacer"></div>
			<div class="verticalSpacer"></div>

			<div class="">
				<input id="buttonBack" type="button" class="button_border" value="<?php echo localize("general_reg_back"); ?>"></input>
				<input id="buttonContinue" type="button" class="button_filled float_right" value="<?php echo localize("general_reg_complete"); ?>"></input>
			</div>

			<div class="verticalSpacer"></div>

			<div class="progress_ind_all">
				<div class="progress_text">
					7/7
				</div>
				<div class="progress_ind_base progress_ind_1"></div>
				<div class="progress_ind_base progress_ind_2"></div>
				<div class="progress_ind_base progress_ind_3"></div>
				<div class="progress_ind_base progress_ind_4"></div>
				<div class="progress_ind_base progress_ind_5"></div>
				<div class="progress_ind_base progress_ind_6"></div>
				<div class="progress_ind_base progress_ind_7"></div>
			</div>

			<div class="panel_reg_fill_bottom">
			</div>
		</div>
	</div>

	<script>
		window.onload = function() {
			var temporaryCode = localStorage.getItem("temporaryCode");
			if(!temporaryCode) {
				document.location = "/index.php";
				return;
			}

			document.getElementById("buttonBack").addEventListener("click", function() {
				buttonNavClicked("back");
			});
			document.getElementById("buttonContinue").addEventListener("click", function() {
				buttonNavClicked("continue");
			});

			/* set viewport to absolute pixels to prevent mobile keyboard from resizing screen */
			setTimeout(function () {
				var viewheight = $(window).height();
				var viewwidth = $(window).width();
				var viewport = $("meta[name=viewport]");
				viewport.attr("content", "height=" + viewheight + "px, width=" + 
				viewwidth + "px, initial-scale=1.0");
			}, 300);
		};

		function hideErrorDivs() {
			
		}

		function buttonNavClicked(nameButton) {
			var inputOk = true;

			var showErrors = (nameButton == "continue");
			if (showErrors)
				hideErrorDivs();

			if (nameButton == "back")
				document.location = "/registration6.php";
			else {
				if(nameButton == "continue") {
					if(inputOk) {
						document.location = "/registration7.php";
					}
				}
			}
		}

	</script>
</body>