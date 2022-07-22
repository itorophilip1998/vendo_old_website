<?php 
	include("translate.php");
	include("configuration.php");
	include("utils.php");
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
				<?php echo localize("reg5_logo_text"); ?>
			</div>

			<?php 
				include("./html_templates/access_package_info.php");
			?>

		</div>


		<div class="reg_container_content">
			<div id="divContentToBlur" class="reg_container_for_blur">
				<div class="panel_logo_logo_input">
					<img src='Images/logo1.png' width=180 height=60></img>
				</div>

				<div class="panel_reg_fill_top">
				</div>

				<div class="regTradingAccountTitle">
					<?php echo localize("reg5_trading_account"); ?>
				</div>

				<div class="verticalSpacer"></div>

				<div class="green">
					<?php 
						$text = localize("reg5_trading_account_text_fee");
						$text = str_replace("[[RegistrationFee]]", REGISTRATION_FEE, $text);
						echo $text;
					?>
				</div>

				<div class="verticalSpacerS"></div>

				<?php 
					include("./html_templates/access_packages_selection.php");
				?>

				<div class="verticalSpacer"></div>

				<div class="panel_fill_vertical">
				</div>

				<div class="">
					<input id="buttonBack" type="button" class="button_border" value="<?php echo localize("general_reg_back"); ?>"></input>
					<input id="buttonContinue" type="button" class="button_filled float_right" value="<?php echo localize("general_reg_next"); ?>"></input>
				</div>

				<div class="verticalSpacer"></div>

				<div class="progress_ind_all">
					<div class="progress_text">
						6/7
					</div>
					<div class="progress_ind_base progress_ind_1"></div>
					<div class="progress_ind_base progress_ind_2"></div>
					<div class="progress_ind_base progress_ind_3"></div>
					<div class="progress_ind_base progress_ind_4"></div>
					<div class="progress_ind_base progress_ind_5"></div>
					<div class="progress_ind_base progress_ind_6"></div>
					<!-- div class="progress_ind_base progress_ind_7"></div-->
				</div>

				<div class="panel_reg_fill_bottom">
				</div>
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
				
			var tradingAccount = localStorage.getItem("tradingAccount");
			selectAccount(tradingAccount);

			/* set viewport to absolute pixels to prevent mobile keyboard from resizing screen */
			setTimeout(function () {
				var viewheight = $(window).height();
				var viewwidth = $(window).width();
				var viewport = $("meta[name=viewport]");
				viewport.attr("content", "height=" + viewheight + ", width=" + 
				viewwidth + ", initial-scale=1.0");
			}, 300);

			document.getElementById("div_modal_close").addEventListener("click", function() {
				hideModal();
			})

		};

		window.onclick = function(event) {
			if (event.target.id != "regTableInfo1" && event.target.id != "regTableInfo2" && event.target.id != "regTableInfo3" && event.target.id != "regTableInfo4" && !event.target.classList.contains("modal_ta_content") && !event.target.parentNode.classList.contains("modal_ta_content") && !event.target.parentNode.parentNode.classList.contains("modal_ta_content") && !event.target.parentNode.parentNode.parentNode.classList.contains("modal_ta_content")) {
				hideModal();
			}
		}


		function hideModal() {
			document.getElementById("divContentToBlur").classList.remove("modal_ta_blur");
		}

		document.getElementById("buttonBack").addEventListener("click", function() { buttonNavClicked("back"); });
		document.getElementById("buttonContinue").addEventListener("click", function() { buttonNavClicked("continue"); });

		function buttonNavClicked(nameButton) {
			var inputOk = true;

			var showErrors = (nameButton == "continue");
			if(showErrors)
				hideErrorDivs();

			localStorage.removeItem("tradingAccount");
			var tradingAccount = getSelectedAccount();
			if(tradingAccount)
				localStorage.setItem("tradingAccount", tradingAccount);
			else {
				if(showErrors)
					document.getElementById("divErrorNoSelection").classList.remove("hidden");
				inputOk = false;
			}

			if(nameButton == "back")
				document.location = "/registration4.php";
			else {
				if(nameButton == "continue") {
					if(inputOk)
						document.location = "/registration6.php";
				}
			}
		}

		function hideErrorDivs() {
			document.getElementById("divErrorNoSelection").classList.add("hidden");
		}

	</script>
</body>