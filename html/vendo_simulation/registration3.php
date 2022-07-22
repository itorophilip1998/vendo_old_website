<?php 
	include("translate.php");
	include("utils.php");

	$emailInUse = $_REQUEST["emailInUse"];
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
				<?php echo localize("reg3_logo_text"); ?>
			</div>
		</div>

		<div class="reg_container_content">
			<div class="panel_logo_logo_input">
				<img src='Images/logo1.png' width=180 height=60></img>
			</div>

			<div class="panel_reg_fill_top">
			</div>

			<div class="textLabelInput">
				<?php echo localize("reg3_contact_info"); ?>
			</div>

			<div class="verticalSpacer"></div>
			
			<div class="row">
				<div class="col-md-4">
					<?= createPhoneCodeHtmlSelect($_SESSION["locale"]) ?>
					<div class="verticalSpacer"></div>
				</div>
				<div class="col-md-8">
					<input type="tel" name="phone" id="inputMobileNumber" autocomplete="tel" placeholder="<?php echo localize("reg3_contact_info_placeholder_phone"); ?>">
					</input>
					<div class="verticalSpacer"></div>
					<div id="divErrorMobileNumberIsMissing" class="hidden">
						<div class="warning-text"><?php echo localize("reg3_contact_info_error_no_phone"); ?></div>
					</div> 
				</div>
			</div>


			<div class="verticalSpacer"></div>

			<input type="email" name="email" id="inputEmail" autocomplete="email" placeholder="<?php echo localize("reg3_contact_info_placeholder_email"); ?>">
			</input>

			<div id="divErrorEmailIsMissing" class="hidden">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("reg3_contact_info_error_no_email"); ?></div>
			</div> 

			<div id="divErrorEmailInvalid" class="hidden">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("reg3_contact_info_error_email_invalid"); ?></div>
			</div> 

			<div id="divErrorEmailInUse" class="<?php if(!$emailInUse) echo "hidden" ?>">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("reg3_contact_info_error_email_in_used"); ?></div>
			</div> 

			<div class="verticalSpacer"></div>

			<input type="email" name="emailRep" id="inputEmailRep" autocomplete="email" placeholder="<?php echo localize("reg3_contact_info_placeholder_email_rep"); ?>">
			</input>

			<div id="divErrorEmailRepMissing" class="hidden">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("reg3_contact_info_error_email_rep_missing"); ?></div>
			</div> 

			<div id="divErrorEmailRepWrong" class="hidden">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("reg3_contact_info_error_email_rep_wrong"); ?></div>
			</div> 

			<div class="panel_fill_vertical">
			</div>

			<div class="">
				<input id="buttonBack" type="button" class="button_border" value="<?php echo localize("general_reg_back"); ?>"></input>
				<input id="buttonContinue" type="button" class="button_filled float_right" value="<?php echo localize("general_reg_next"); ?>"></input>
			</div>

			<div class="verticalSpacer"></div>

			<div class="progress_ind_all">
				<div class="progress_text">
					4/7
				</div>
				<div class="progress_ind_base progress_ind_1"></div>
				<div class="progress_ind_base progress_ind_2"></div>
				<div class="progress_ind_base progress_ind_3"></div>
				<div class="progress_ind_base progress_ind_4"></div>
				<!-- div class="progress_ind_base progress_ind_5"></div>
				<div class="progress_ind_base progress_ind_6"></div>
				<div class="progress_ind_base progress_ind_7"></div-->
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
				
			var mobileNumber = localStorage.getItem("mobileNumber");
			document.getElementById("inputMobileNumber").value = mobileNumber;

			var email = localStorage.getItem("email");
			document.getElementById("inputEmail").value = email;
			document.getElementById("inputEmailRep").value = email;

			var countryCode = localStorage.getItem("country");
			if(countryCode && countryCode!="null")
			{
				document.getElementById("phoneCodeSelect").value = countryCode;
			}

			var countryCodeForPhonecode = localStorage.getItem("phoneCodeSelect");
			if(countryCodeForPhonecode)
			{
				document.getElementById("phoneCodeSelect").value = countryCodeForPhonecode;
			}

			/* set viewport to absolute pixels to prevent mobile keyboard from resizing screen */
			setTimeout(function () {
				var viewheight = $(window).height();
				var viewwidth = $(window).width();
				var viewport = $("meta[name=viewport]");
				viewport.attr("content", "height=" + viewheight + "px, width=" + 
				viewwidth + "px, initial-scale=1.0");
			}, 300);
		};

		document.getElementById("buttonBack").addEventListener("click", function() { buttonNavClicked("back"); });
		document.getElementById("buttonContinue").addEventListener("click", function() { buttonNavClicked("continue"); });

		function buttonNavClicked(nameButton) {
			var inputOk = true;

			var showErrors = (nameButton == "continue");
			if(showErrors)
				hideErrorDivs();


			var countryCodeForPhoneCode = document.getElementById("phoneCodeSelect").value;
			if(countryCodeForPhoneCode) {
				localStorage.setItem("phoneCodeSelect", countryCodeForPhoneCode);
			}

			localStorage.removeItem("mobileNumber");
			trimTextOfInput("inputMobileNumber");
			var mobileNumber = document.getElementById("inputMobileNumber").value;
			if(mobileNumber)
				localStorage.setItem("mobileNumber", mobileNumber);
			else {
				if(showErrors)
					document.getElementById("divErrorMobileNumberIsMissing").classList.remove("hidden");
				inputOk = false;
			}

			localStorage.removeItem("email");
			trimTextOfInput("inputEmail");
			trimTextOfInput("inputEmailRep");
			var email = document.getElementById("inputEmail").value;
			var emailRep = document.getElementById("inputEmailRep").value;
			if(email)
				if(!validateEmail(email)) {
					if(showErrors)
						document.getElementById("divErrorEmailInvalid").classList.remove("hidden");
					inputOk = false;
				} else {
					if(!emailRep) {
						if(showErrors)
							document.getElementById("divErrorEmailRepMissing").classList.remove("hidden");
						inputOk = false;
					} else if(emailRep != email) {
						if(showErrors)
							document.getElementById("divErrorEmailRepWrong").classList.remove("hidden");
						inputOk = false;
					} else {
						localStorage.setItem("email", email);
					}
				}
			else {
				if(showErrors)
					document.getElementById("divErrorEmailIsMissing").classList.remove("hidden");
				inputOk = false;
			}

			if(nameButton == "back")
				document.location = "/registration2.php";
			else {
				if(nameButton == "continue") {
					if(inputOk) {
						document.location = "/registration4.php";
					}
				}
			}
		}

		function hideErrorDivs() {
			document.getElementById("divErrorMobileNumberIsMissing").classList.add("hidden");
			document.getElementById("divErrorEmailIsMissing").classList.add("hidden");
			document.getElementById("divErrorEmailInUse").classList.add("hidden");
			document.getElementById("divErrorEmailInvalid").classList.add("hidden");
			document.getElementById("divErrorEmailRepMissing").classList.add("hidden");
			document.getElementById("divErrorEmailRepWrong").classList.add("hidden");
		}

	</script>
</body>