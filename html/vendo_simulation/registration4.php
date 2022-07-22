<?php 
	include("translate.php");
	if(session_status() == PHP_SESSION_NONE){
		session_start();
	}
	$language = strtolower(isset($_SESSION["locale"])?$_SESSION["locale"]:LANGUAGE_DEFAULT);
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
				<?php echo localize("reg4_logo_text"); ?>
			</div>
		</div>

		<div class="reg_container_content">
			<div id="divContentToBlur" class="reg_container_for_blur">
				<div class="panel_logo_logo_input">
					<img src='Images/logo1.png' width=180 height=60></img>
				</div>

				<div class="panel_reg_fill_top">
				</div>

				<div class="textLabelInput">
					<?php echo localize("reg4_password"); ?>
				</div>

				<div class="verticalSpacer"></div>

				<input id="inputPassword" type="password" autocomplete="off" placeholder="<?php echo localize("reg4_password_placeholder"); ?>">
				</input>

				<div id="divErrorPasswordIsMissing" class="hidden">
					<div class="verticalSpacer"></div>
					<div class="warning-text"><?php echo localize("reg4_password_error_missing"); ?></div>
				</div> 

				<div class="verticalSpacer"></div>

				<input id="inputPassword2" type="password" autocomplete="off" placeholder="<?php echo localize("reg4_password_placeholder_2"); ?>">
				</input>

				<div id="divErrorPassword2IsMissing" class="hidden">
					<div class="verticalSpacer"></div>
					<div class="warning-text"><?php echo localize("reg4_password_error_missing_2"); ?></div>
				</div>

				<div id="divErrorPasswordsAreDifferent" class="hidden">
					<div class="verticalSpacer"></div>
					<div class="warning-text"><?php echo localize("reg4_password_error_different"); ?></div>
				</div>

				<div class="verticalSpacer"></div>
				<div class="verticalSpacer"></div>

				<div>
					<label class="container_radio"> <input class="pointer" type="checkbox" id="radio_accept_cond"
						name="radio_accept_cond" value="1"> <span
						id="radio_accept_cond_1" class="checkmark pointer"></span>
						<span id="radio_accept_cond_text" class="span_text fontSizeAcceptConditions"><?php echo localize("reg4_accept_conditions"); ?></span>
					</label>
				</div>
				
				<div id="divErrorAcceptConditions" class="hidden">
					<div class="verticalSpacer"></div>
					<div class="warning-text"><?php echo localize("reg4_accept_conditions_error"); ?></div>
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
						5/7
					</div>
					<div class="progress_ind_base progress_ind_1"></div>
					<div class="progress_ind_base progress_ind_2"></div>
					<div class="progress_ind_base progress_ind_3"></div>
					<div class="progress_ind_base progress_ind_4"></div>
					<div class="progress_ind_base progress_ind_5"></div>
					<!-- div class="progress_ind_base progress_ind_6"></div>
					<div class="progress_ind_base progress_ind_7"></div-->
				</div>

				<div class="panel_reg_fill_bottom">
				</div>
			</div>

			<div id="modal_terms_conditions" class="modal_terms">
				<!-- Modal content -->
				<div class="modal_terms_content">
					<div id="div_modal_terms_close" class="modal_terms_close">
						&times;
						<!--span class="modal_ta_close_span">&times;</span -->
					</div>
					
					<div>
						<?php echo file_get_contents("html_templates/terms_conditions_$language.html"); ?>
					</div>

					<div class="verticalSpacer"></div>

					<input id="buttonAcceptTermsConds" type="button" class="button_filled" value="<?php echo localize("general_reg_accept"); ?>"></input>
				</div>
			</div>

			<div id="modal_privacy_policy" class="modal_terms">
				<!-- Modal content -->
				<div class="modal_terms_content">
					<div id="div_modal_privacy_close" class="modal_terms_close">
						&times;
						<!--span class="modal_ta_close_span">&times;</span -->
					</div>
					
					<div>
						<?php 
							echo file_get_contents("html_templates/privacy_policy_$language.html"); 
						?>
					</div>

					<div class="verticalSpacer"></div>

					<input id="buttonAcceptPrivacyPolicy" type="button" class="button_filled" value="<?php echo localize("general_reg_accept"); ?>"></input>
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
				
			var password = localStorage.getItem("password");
			document.getElementById("inputPassword").value = password;
			document.getElementById("inputPassword2").value = password;

			var acceptedConditions = localStorage.getItem("acceptedConditions") == "true";
			document.getElementById("radio_accept_cond").checked = acceptedConditions;
			
			document.getElementById("span_terms_conds").addEventListener("click", function(event) {
				event.preventDefault();
				document.getElementById("modal_terms_conditions").style.display = "block";
				document.getElementById("divContentToBlur").classList.add("modal_terms_blur");
			});
			document.getElementById("div_modal_terms_close").addEventListener("click", function() {
				hideModal();
			})
			document.getElementById("buttonAcceptTermsConds").addEventListener("click", function() {
				document.getElementById("radio_accept_cond").checked = "true";
				hideModal();
			});

			document.getElementById("span_private_policy").addEventListener("click", function(event) {
				event.preventDefault();
				document.getElementById("modal_privacy_policy").style.display = "block";
				document.getElementById("divContentToBlur").classList.add("modal_terms_blur");
			});
			document.getElementById("div_modal_privacy_close").addEventListener("click", function() {
				hideModal();
			})
			document.getElementById("buttonAcceptPrivacyPolicy").addEventListener("click", function() {
				document.getElementById("radio_accept_cond").checked = "true";
				hideModal();
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

		window.onclick = function(event) {
			if (event.target.id != "span_terms_conds" && event.target.id != "span_private_policy" && !event.target.classList.contains("modal_terms_content") && !event.target.parentNode.classList.contains("modal_terms_content") && !event.target.parentNode.parentNode.classList.contains("modal_terms_content") && !event.target.parentNode.parentNode.parentNode.classList.contains("modal_terms_content")) {
				hideModal();
			}
		}

		function hideModal() {
			document.getElementById("modal_terms_conditions").style.display = "none";
			document.getElementById("modal_privacy_policy").style.display = "none";
			document.getElementById("divContentToBlur").classList.remove("modal_terms_blur");
		}

		document.getElementById("buttonBack").addEventListener("click", function() { buttonNavClicked("back"); });
		document.getElementById("buttonContinue").addEventListener("click", function() { buttonNavClicked("continue"); });

		document.getElementById("inputPassword").addEventListener("keydown", passwordKeyPressed);
		document.getElementById("inputPassword2").addEventListener("keydown", passwordKeyPressed);

		function passwordKeyPressed() {
			document.getElementById("divErrorPasswordsAreDifferent").classList.add("hidden");
		}

		function buttonNavClicked(nameButton) {
			var inputOk = true;

			var showErrors = (nameButton == "continue");
			if(showErrors)
				hideErrorDivs();

			localStorage.removeItem("acceptedConditions");
			var acceptCond = document.getElementById("radio_accept_cond").checked;
			if(acceptCond) {
				localStorage.setItem("acceptedConditions", "true");
			} else {
				if(showErrors)
					document.getElementById("divErrorAcceptConditions").classList.remove("hidden");
				inputOk = false;
			}

			localStorage.removeItem("password");
			trimTextOfInput("inputPassword");
			trimTextOfInput("inputPassword2");
			var password = document.getElementById("inputPassword").value;
			var password2 = document.getElementById("inputPassword2").value;
			if(password && password2) {
				if(password == password2)
					localStorage.setItem("password", password);
				else {
					if(showErrors)
						document.getElementById("divErrorPasswordsAreDifferent").classList.remove("hidden");
					inputOk = false;
				}
			} else {
				if(showErrors) {
					if(!password)
						document.getElementById("divErrorPasswordIsMissing").classList.remove("hidden");
					if(!password2)
						document.getElementById("divErrorPassword2IsMissing").classList.remove("hidden");
				}
				inputOk = false;
			}

			if(nameButton == "back")
				document.location = "/registration3.php";
			else {
				if(nameButton == "continue") {
					if(inputOk)
						document.location = "/registration5.php";
				}
			}
		}

		function hideErrorDivs() {
			document.getElementById("divErrorPasswordsAreDifferent").classList.add("hidden");
			document.getElementById("divErrorPasswordIsMissing").classList.add("hidden");
			document.getElementById("divErrorPassword2IsMissing").classList.add("hidden");
			document.getElementById("divErrorAcceptConditions").classList.add("hidden");
		}

	</script>
</body>