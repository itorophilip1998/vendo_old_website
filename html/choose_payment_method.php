<?php
	include_once("translate.php");
    include_once("configuration.php");
    include_once("db.php");
    include_once("utils.php");
    
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $registrationFeePost = $_POST['registrationFeePost'];
    $AccountType = $_POST['AccountTypePost'];

    if(isset($registrationFeePost) && isset($AccountType)) {
        if($registrationFeePost == "false") {
            session_start(); // start or continue a session
			$loggedInAsSponsorId = $_SESSION["loggedInAsUserId"];
			if (!$loggedInAsSponsorId) {
				$result["message"] = "Your session has expired, please log in again.";
				$result["code"] = -10;
				$jsonOut=json_encode($result);		
				die($jsonOut);            
            }
            
            include "bo_read_user_logged_in.php";
            
            $amountPaid = getOriginalPaidAmountFromAccess($user['trading_account']);
            $AccountFee = getPaidAmountFromAccess($AccountType);
            $AccountType = getNameTradingAccount($AccountType);
            $AccountFee = $AccountFee - $amountPaid;
            $amountToPay = $AccountFee;
        }
    }
?>

<div class="panel_reg_fill_top">
</div>

<div class="textLabelInput">
    <?php echo localize("reg6_payment_method"); ?>
</div>

<div class="verticalSpacer"></div>
<div class="verticalSpacer"></div>

<div>
    <label class="container_radio"> <input type="radio" name="radio_payment_method" value="1" disabled> <span id="radio_payment_method_1" class="checkmark grey"></span>
        <span class="span_text" style="color: #3E4145 !important;"><?php echo localize("reg6_payment_method_credit_card"); ?></span>
    </label>
</div>

<div class="verticalSpacer"></div>

<div>
    <label class="container_radio"> <input type="radio" name="radio_payment_method" value="2" disabled> <span id="radio_payment_method_3" class="checkmark grey"></span>
        <span class="span_text" style="color: #3E4145 !important;"><?php echo localize("reg6_payment_method_wire_transfer"); ?></span>
    </label>
</div>

<div class="verticalSpacer"></div>

<div>
    <label class="container_radio"> <input type="radio" name="radio_payment_method" value="3" checked> <span id="radio_payment_method_4" class="checkmark"></span>
        <span class="span_text"><?php echo localize("reg6_payment_method_crypto"); ?></span>
    </label>

    <div class="subradio">
        <div>
            <label class="container_radio"> <input type="radio" name="radio_payment_currency" value="USDT" checked> <span id="radio_payment_currency_1" class="checkmark"></span>
                <span class="span_text"><?php echo localize("reg6_payment_currency_tether"); ?></span>
            </label>
        </div>

        <div>
            <label class="container_radio"> <input type="radio" name="radio_payment_currency" value="USDT.ERC20"> <span id="radio_payment_currency_1" class="checkmark"></span>
                <span class="span_text"><?php echo localize("reg6_payment_currency_tether_erc20"); ?></span>
            </label>
        </div>

        <div>
            <label class="container_radio"> <input type="radio" name="radio_payment_currency" value="TUSD"> <span id="radio_payment_currency_2" class="checkmark"></span>
                <span class="span_text"><?php echo localize("reg6_payment_currency_trueusd"); ?></span>
            </label>
        </div>

        <div>
            <label class="container_radio"> <input type="radio" name="radio_payment_currency" value="BTC"> <span id="radio_payment_currency_3" class="checkmark"></span>
                <span class="span_text"><?php echo localize("reg6_payment_currency_bitcoin"); ?></span>
            </label>
        </div>
        
        <div>
            <label class="container_radio"> <input type="radio" name="radio_payment_currency" value="ETH"> <span id="radio_payment_currency_4" class="checkmark"></span>
                <span class="span_text"><?php echo localize("reg6_payment_currency_ethereum"); ?></span>
            </label>
        </div>   

        <?php if (false): //activate for testing purposes?>
        <div>
            <label class="container_radio"> <input type="radio" name="radio_payment_currency" value="LTCT"> <span id="radio_payment_currency_5" class="checkmark"></span>
                <span class="span_text">LTCT (Testwährung)</span>
            </label>
        </div>      
        <?php endif; ?>
    </div>
</div>

<div class="verticalSpacer"></div>

<div>
    <label class="container_radio"> <input type="radio" name="radio_payment_method" value="4"> <span id="radio_payment_method_5" class="checkmark"></span>
        <span class="span_text"><?php echo localize("reg6_payment_method_banxa"); ?></span>
    </label>
</div>

<div id="divErrorNoPaymentMethod" class="hidden">
    <div class="verticalSpacer"></div>
    <div class="warning-text"><?php echo localize("reg6_payment_method_error"); ?></div>
</div>

<div class="verticalSpacer"></div>
<div class="verticalSpacer"></div>

<table>
    <tbody>
        <tr class="regTablePaymentTextPosition">
            <td id="table_cell_account_type" class="regTablePaymentCol0">
                <?= $AccountType ?>
            </td>
            <td id="table_cell_account_fee" class="regTablePaymentCol1">
                <?=  " $ " . number_format($AccountFee, 2, ".", ",") ?>
            </td>
        </tr>

        <?php if($registrationFee): ?>
            <tr class="regTablePaymentTextPosition">
                <td class="regTablePaymentCol0">
                    <?php echo localize("reg6_table_registration_fee"); ?>
                </td>
                <td id="table_cell_registration_fee" class="regTablePaymentCol1">
                    <?= " $ " . $registrationFeeAmount ?>
                </td>
            </tr>
        <?php endif; ?>

        <tr class="regTablePaymentTrBorder">
            <td></td>
            <td></td>
        </tr>
        <tr class="">
            <td class="regTablePaymentCol0 regTablePaymentSumText">
                <?php echo localize("reg6_table_sum"); ?>
            </td>
            <td id="table_cell_sum" class="regTablePaymentCol1 regTablePaymentSumNumber">
                <?= " $ " . number_format($amountToPay, 2, ".", ",") ?>
            </td>
        </tr>
    </tbody>
</table>

<div class="verticalSpacer"></div>
<div class="verticalSpacer"></div>

<div class="panel_fill_vertical">
</div>

<div class="">
    <input id="buttonBack" type="button" class="button_border" value="<?php echo localize("general_reg_back"); ?>"></input>
    <input id="buttonContinue" type="button" class="button_filled float_right" value="<?php echo localize("general_reg_paynow"); ?>"></input>
</div>

<div class="verticalSpacer"></div>

<div class="panel_reg_fill_bottom">
</div>

<script>
			var iPaymentMethod = localStorage.getItem("paymentMethod");
			if (iPaymentMethod) {
				var radios = document.getElementsByName("radio_payment_method");
				for (var iR = 0; iR < radios.length; iR++) {
					if (radios[iR].value == iPaymentMethod) {
						radios[iR].checked = "checked";
					}
				}

				if (iPaymentMethod == 3) //crypto
				{
					var iPaymentCurrency = localStorage.getItem("paymentCurrency");
					if (iPaymentCurrency)
					{
						var radiosCur = document.getElementsByName("radio_payment_currency");
						for (var iC = 0; iC < radiosCur.length; iC++) {
							if (iPaymentCurrency == radiosCur[iC].value) {
								radiosCur[iC].checked = "checked";
								break;
							}
						}
					}    
				}       
				else
				{
					$(".subradio").hide();
				}			
			}

			$("input[name='radio_payment_method']").click(function() {
				var selectedMethod = $("input[name='radio_payment_method']:checked").val();
				if (selectedMethod == 3)
				{
					$(".subradio").slideDown();
				}
				else
				{
					$(".subradio").slideUp();
				}
			});
            
</script>