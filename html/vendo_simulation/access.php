<?php 
	include("translate.php");
    include("configuration.php");
    include("db.php");
    include("utils.php");
    include("bitcoin.php");
    
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    include 'bo_read_user_logged_in.php';
    // is logged in

    if($user['broker_registration_complete'] != 1) {
        redirect(ROOT_URL . "bo_profile.php");
    }

    $payment = BitCoin::getPendingPayment($user['id']);

    // If payment has a timeout -> set openBuyingTransaction to expired - so a new one can be made
    $paymentTimeout = false;
    if($payment['status'] == "timeout") {
        $paymentTimeout = true;
        BitCoin::updatePaymentStatus($payment['payment_id'], "expired");
    }
    if($payment['status'] == "expired") {
        $paymentTimeout = true;
    }

?>

<html>
<head>
	<title>Vendo</title>
		
	<?php
		echo file_get_contents("bo_head.html");		
	?>		
</head>
<body>
	<div class="reg_container_all" style="overflow: hidden;">
		<div class="reg_container_logo">
			<div class="panel_logo_logo">
                <a href="./bo_profile.php"><img src='Images/logo1.png' height=60 accept="image/*"></img></a>
			</div>
			<div class="panel_logo_text">
				<?php echo localize("reg5_logo_text"); ?>
			</div>

            <?php 
                if($user['trading_account'] < 4) {
                    include("./html_templates/access_package_info.php");
                }
			?>

		</div>


		<div class="reg_container_content">
            <div id="loadingMarquee" class="loadingOverlay" style="display: none;">
                <img src="./Images/loading.gif" class="loadingGif">
            </div>
			<div id="divDynamicContent" class="reg_container_for_blur main-color">
				<div class="panel_logo_logo_input">
					<img src='Images/logo1.png' width=180 height=60></img>
				</div>

				<div class="panel_reg_fill_top">
				</div>



                <?php if($user['trading_account'] < 4): ?>
                        <?php if($payment['status'] != "pending"): ?>
                                <div class="regTradingAccountTitle">
                                    <?php echo localize("reg5_trading_account"); ?>
                                </div>
                                <div class="verticalSpacer"></div>

                            <?php  
                                include("./html_templates/access_packages_selection.php");

                            else:
                                echo "<div>" . localize('bo_payment_already_in_progress', $user['language']) . "</div>";
                            ?>
                                <div><?= localize('reg6_table_sum', $user['language']) ?><a class="red_bitcoin_failed no_link_decoration" href="<?= $payment['link'] ?>" target="_blank">&nbsp;&nbsp;<?= localize('bo_click_here', $user['language']) ?></a></div>
                            <?php endif;
                    else:
                        $text = localize('bo_already_highest_access', $user['language']);
                        $text = str_replace(":HighesAccess", getNameTradingAccount(4), $text); ?>
                        <div class="bo_text_profile_greeting">
                            <?= $text ?>
                        </div>
                <?php endif; ?>

				<div class="verticalSpacer"></div>

				<div class="panel_fill_vertical">
                </div>
                
				<div class="">
                    <input type="button" onclick="buttonNavClicked('backtoProfile')" class="button_filled button_transparent inline button_text_modal_orange button_modal_outline no_shadow close_form" value="<?php echo localize("general_reg_back", $user['language']); ?>">
                    <?php if(($user['trading_account'] < 4) && $payment['status'] != "pending"): ?>
                        <input type="button" onclick="buttonNavClicked('continue')" class="ml-auto div_modal_warning_close button_filled inline button_text_modal_black float_right" value="<?php echo localize("general_reg_next", $user['language']); ?>">
                    <?php endif; ?>
				</div>

				<div class="panel_reg_fill_bottom">
				</div>
			</div>
        </div>
        

	</div>

    <div id="messagebox" class="messagebox" style="display: none;">
        <div class="messagetext">
        </div>
    </div>

	
	<script>
		$(document).ready(function() {

			/* set viewport to absolute pixels to prevent mobile keyboard from resizing screen */
			setTimeout(function () {
				var viewheight = $(window).height();
				var viewwidth = $(window).width();
				var viewport = $("meta[name=viewport]");
				viewport.attr("content", "height=" + viewheight + "px, width=" + 
				viewwidth + "px, initial-scale=1.0");
			}, 300);
		});

		window.onclick = function(event) {
			if (event.target == document.getElementById("modal_trading_account_info")) {
				hideModal();
			}
		}

        function buttonNavClicked(nameButton) {
            if(nameButton == "continue") {
                var tradingAccount = getSelectedAccount();
                
                // If no valid account was chosen - show error
                if((!tradingAccount) || (tradingAccount <= <?= $user['trading_account'] ?>) || (tradingAccount > 4)) {
                    document.getElementById("divErrorNoSelection").classList.remove("hidden");
                } else {
                    $("#loadingMarquee").show();
            
                    $.post("choose_payment_method.php", {
                    	registrationFeePost: false,
                    	AccountTypePost: tradingAccount
                    }, function(data) {
                        $("#loadingMarquee").hide();
                        try {
                            let jsonData = JSON.parse(data);
                            if(jsonData.code == (-10)) {
                                window.location = "./bo_login.php?destination=" + encodeURI("./access.php");
                                return;
                            }
                        } catch (error) {
                            // silent
                        }

                        $("#divDynamicContent").html(data);

                        $("#buttonBack").click(function(e) {
                            e.preventDefault();

                            window.location = "./access.php";
                        });

                        $("#buttonContinue").click(function(e){
                            e.preventDefault();
                            $("#loadingMarquee").show();
                            var iPaymentMethod = 3;

                            var radios = document.getElementsByName("radio_payment_method");
                            for (var iR = 0; iR < radios.length; iR++) {
                                if (radios[iR].checked) {
                                    iPaymentMethod = parseInt(radios[iR].value);
                                    break;
                                }
                            } 

                            var iPaymentCurrency = 'USDT.ERC20';
                            if (iPaymentMethod == 3)
                            {
                                var radiosCur = document.getElementsByName("radio_payment_currency");
                                for (var iC = 0; iC < radiosCur.length; iC++) {
                                    if (radiosCur[iC].checked) {
                                        iPaymentCurrency = radiosCur[iC].value;
                                        break;
                                    }
                                }
                            }                                     
                            
                            $.post("registration_payment.php", {
                                ajaxCall: true,
                                tradingAccount: tradingAccount,
                                userId: <?= $user['id'] ?>,
                                oldtradingAccount: <?= $user['trading_account'] ?>,
                                paymentMethod: iPaymentMethod,
                                currency: iPaymentCurrency
                            }, function(data) {
                                var _data = JSON.parse(data);
                                if(_data.code == (200)) {
                                    $("#divDynamicContent").hide(200, function() {
                                        $("#loadingMarquee").hide();
                                        $("#divDynamicContent").html("<div class='main-color container fit-content'>\
                                                                    <div class='verticalSpacer'></div>\
                                                                    <div class='verticalSpacer'></div>" + 
                                                                    _data.data + 
                                                                    "<div class='verticalSpacer'></div>\
                                                                    <div class='verticalSpacer'></div>\
                                                                    <div>\
                                                                        <a href='/bo_main.php' id='buttonContinue' type='button' role='button' class='btn button_filled'><?php echo localize("general_reg_complete"); ?></a>\
                                                                    </div>");
                                        $("#divDynamicContent").show(200);
                                    });
                                } else {
                                    window.location = "./bo_login.php?destination=" + encodeURI("./access.php");
                                }
                            });
                        });
                    });
                }
            } else {
                window.history.back();
                //window.location = "./bo_profile.php";
            }
		}

		function hideErrorDivs() {
			document.getElementById("divErrorNoSelection").classList.add("hidden");
		}

	</script>
</body>