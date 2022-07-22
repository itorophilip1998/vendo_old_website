<?php
	include("translate.php");
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
				<?php echo localize("reg1_logo_text"); ?>
			</div>
		</div>

		<div class="reg_container_content">
			<div class="panel_logo_logo_input">
				<img src='Images/logo1.png' width=180 height=60></img>
			</div>

			<div class="panel_reg_fill_top">
			</div>

			<div>
				<label class="container_radio widthRadioSex"> <input type="radio"
					name="radio_sex" value="0" class="pointer"> <span
					id="radio_sex_0" class="checkmark pointer"></span>
					<span class="span_text"><?php echo localize("reg1_sex_male"); ?></span>
				</label>
				<div>
				</div>
				<label class="container_radio widthRadioSex pointer"> <input type="radio"
					name="radio_sex" value="1" class="pointer"> <span
					id="radio_sex_1" class="checkmark pointer"></span>
					<span class="span_text"><?php echo localize("reg1_sex_female"); ?></span>
				</label>
				<div>
				</div>
			</div>

			<div id="divErrorSexIsMissing" class="hidden">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("reg1_sex_error_missing"); ?></div>
			</div> 

			<div class="verticalSpacer"></div>
			<div class="verticalSpacer"></div>
			
			<div class="textLabelInput">
				<?php echo localize("reg1_name"); ?>
			</div>

			<div class="verticalSpacer"></div>

			<div class="regNameContainer">
				<div class="regNameWidthGivenName">
					<input id="inputGivenName" type="text" autocomplete="given-name" placeholder="<?php echo localize("reg1_birthday_placeholder_given_name"); ?>">
					</input>
				</div>

				<div class="regNameWidthSurName">
					<input id="inputSurName" type="text" autocomplete="family-name" placeholder="<?php echo localize("reg1_birthday_placeholder_sur_name"); ?>">
					</input>
				</div>
			</div>

			<div id="divErrorNameIsMissing" class="hidden">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("reg1_name_error_missing"); ?></div>
			</div> 

			<div class="verticalSpacer"></div>
			<div class="verticalSpacer"></div>

			<div class="textLabelInput">
				<?php echo localize("reg1_birthday"); ?>
			</div>
			
			<div class="verticalSpacer"></div>

			<div class="regBirthdayContainer">
				<div class="regBirthdayWidthDay">
					<input id="inputBirthdayDay" type="number" autocomplete="bday-day" class="alignCenter" min="1" max="31" placeholder="<?php echo localize("reg1_birthday_placeholder_day"); ?>">
					</input>
				</div>
				<div class="regBirthdayWidthMonth">
					<input id="inputBirthdayMonth" type="number" autocomplete="bday-month" class="alignCenter" min="1" max="12" placeholder="<?php echo localize("reg1_birthday_placeholder_month"); ?>">
					</input>
				</div>
				<div class="regBirthdayWidthYear">
					<input id="inputBirthdayYear" type="number" autocomplete="bday-year" class="alignCenter" min="1900" max="2100" placeholder="<?php echo localize("reg1_birthday_placeholder_year"); ?>">
					</input>
				</div>
			</div>

			<div id="divErrorBirthday" class="hidden">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("reg1_birthday_error_invalid"); ?></div>
			</div> 

			<div id="divErrorBirthday18" class="hidden">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("reg1_birthday_error_18"); ?></div>
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
					2/7
				</div>
				<div class="progress_ind_base progress_ind_1"></div>
				<div class="progress_ind_base progress_ind_2"></div>
				<!--div class="progress_ind_base progress_ind_3"></div>
				<div class="progress_ind_base progress_ind_4"></div>
				<div class="progress_ind_base progress_ind_5"></div>
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

			var iSex = localStorage.getItem("sex");
			if(iSex) {
				var radios = document.getElementsByName("radio_sex");		
				for (var iR = 0; iR < radios.length; iR++) {
					if(radios[iR].value == iSex) {
						radios[iR].checked = "checked";
					}
				}
			}

			var givenName = localStorage.getItem("givenName");
			document.getElementById("inputGivenName").value = givenName;

			var surName = localStorage.getItem("surName");
			document.getElementById("inputSurName").value = surName;

			var dateOfBirthText2 = localStorage.getItem("dateOfBirth");
			if(dateOfBirthText2) {
				var dateOfBirthMoment = moment(dateOfBirthText2, "YYYY-MM-DD HH:mm:ss");
				document.getElementById("inputBirthdayDay").value = dateOfBirthMoment.format("D");
				document.getElementById("inputBirthdayMonth").value = dateOfBirthMoment.format("M");
				document.getElementById("inputBirthdayYear").value = dateOfBirthMoment.format("YYYY");
			}

			document.getElementById("buttonBack").addEventListener("click", function() { buttonNavClicked("back"); });
			document.getElementById("buttonContinue").addEventListener("click", function() { buttonNavClicked("continue"); });
			
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
			document.getElementById("divErrorSexIsMissing").classList.add("hidden");
			document.getElementById("divErrorNameIsMissing").classList.add("hidden");
			document.getElementById("divErrorBirthday").classList.add("hidden");
			document.getElementById("divErrorBirthday18").classList.add("hidden");
		}

        function buttonNavClicked(nameButton) {
            var inputOk = true;

			var showErrors = (nameButton == "continue");
			if(showErrors)
				hideErrorDivs();

			localStorage.removeItem("sex");
			var iSex = 0;
			var radios = document.getElementsByName("radio_sex");		
			for (var iR = 0; iR < radios.length; iR++) {
				if(radios[iR].checked) {
					iSex = radios[iR].value;
					break;
				}
			}
			if(["0", "1"].includes(iSex))	
				localStorage.setItem("sex", iSex);
			else {
				if(showErrors)
					document.getElementById("divErrorSexIsMissing").classList.remove("hidden");
				inputOk = false;
			}

			localStorage.removeItem("givenName");
			trimTextOfInput("inputGivenName");
			var givenName = document.getElementById("inputGivenName").value;
			if(givenName)
				localStorage.setItem("givenName", givenName);
			else {
				if(showErrors)
					document.getElementById("divErrorNameIsMissing").classList.remove("hidden");
				inputOk = false;
			}

			localStorage.removeItem("surName");
			trimTextOfInput("inputSurName");
			var surName = document.getElementById("inputSurName").value;
			if(surName)
				localStorage.setItem("surName", surName);
			else {
				if(showErrors)
					document.getElementById("divErrorNameIsMissing").classList.remove("hidden");
				inputOk = false;
			}

			localStorage.removeItem("dateOfBirth");
			var day = parseInt(document.getElementById("inputBirthdayDay").value);
			var month = parseInt(document.getElementById("inputBirthdayMonth").value);
			var year = parseInt(document.getElementById("inputBirthdayYear").value);
			var birthdayMoment = moment({ day: day, month: month - 1, year: year });
			var ageYears = moment().diff(birthdayMoment, 'years');
			if(day >= 1 && day <=31 && month >= 1 && month <= 12 && year > 1900 && year < 2100 && birthdayMoment.isValid() &&
				birthdayMoment.format("D") == day && birthdayMoment.format("M") == month && birthdayMoment.format("YYYY") == year) {
				if(ageYears < 18) {
					if(showErrors)
						document.getElementById("divErrorBirthday18").classList.remove("hidden");
					inputOk = false;
				} else {
					var birthdayText = birthdayMoment.format("YYYY-MM-DD 00:00:00");
					localStorage.setItem("dateOfBirth", birthdayText);
				}
			} else {
				if(showErrors)
					document.getElementById("divErrorBirthday").classList.remove("hidden");
				inputOk = false;
			}

            if(nameButton == "back")
				document.location = "/registration0.php";
			else {
				if(nameButton == "continue") {
					if(inputOk) {
						document.location = "/registration2.php";
					}
				}
			}
        }

	</script>
</body>