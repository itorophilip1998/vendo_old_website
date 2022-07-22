<?php 
	require_once("configuration.php");
	include_once("translate.php");
	require_once("db.php");

	$pdo = getDatabase();

	if(session_status() == PHP_SESSION_NONE){
		session_start();
	}
	error_log("Language: ".$_SESSION["locale"]);
	$countries = getAllCountries($pdo, $_SESSION["locale"]);

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
				<?php echo localize("reg2_logo_text"); ?>
			</div>
		</div>

		<div class="reg_container_content">

			<div class="panel_logo_logo_input">
				<img src='Images/logo1.png' width=180 height=60></img>
			</div>

			<div class="panel_reg_fill_top">
			</div>

			<div class="textLabelInput">
				<?php echo localize("reg2_address"); ?>
			</div>

			<div class="verticalSpacer"></div>



			<select class="" id="dlCountries">
					<option disabled selected value class="hidden"><?php echo localize("reg2_address_placeholder_country"); ?></option>
			<?php
				foreach ($countries as $country) {
					$name = isset($country['name'])?$country['name']:$country['nicename'];
					echo '<option value="'.$country['iso'].'">'.$name.'</option>';
				}
			?>
			</select>

			<div id="divErrorCountryIsMissing" class="hidden">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("reg2_address_error_missing"); ?></div>
			</div> 

			<div id="divDeclareNoAdvertisement">
				<div class="verticalSpacer"></div>

				<label class="container_radio"> <input type="checkbox" id="check_no_advertisement"
					name="check_no_advertisement" value="1" class="pointer"> <span
					class="checkmark pointer"></span>
					<span id="radio_accept_cond_text" class="span_text fontSizeAcceptConditions"><?php echo localize("reg2_declare_no_advertisement"); ?></span>
				</label>

				<div id="divErrorGotAdvertisement" class="hidden">
					<div class="verticalSpacer"></div>
					<div class="warning-text"><?php echo localize("reg2_error_got_advertisement"); ?></div>
				</div> 
			</div>

			<div class="verticalSpacer"></div>
			<div class="verticalSpacer"></div>

			<div class="regCityZipContainer">
				<div class="regCityZipCity">
					<input id="inputCity" type="text" autocomplete="address-level2" class="" placeholder="<?php echo localize("reg2_address_placeholder_city"); ?>">
					</input>
				</div>
				<div class="regCityZipZip">
					<input id="inputZip" type="text" autocomplete="postal-code" class="alignCenter" placeholder="<?php echo localize("reg2_address_placeholder_zip"); ?>">
					</input>
				</div>
			</div>

			<div id="divErrorCityIsMissing" class="hidden">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("reg2_address_error_city"); ?></div>
			</div> 

			<div id="divErrorZipIsMissing" class="hidden">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("reg2_address_error_zip"); ?></div>
			</div> 

			<div class="verticalSpacer"></div>

			<div class="regAddressNumberContainer">
				<div class="regAddressNumberAddress">
					<input id="inputAddress" type="text" autocomplete="address-line1" placeholder="<?php echo localize("reg2_address_placeholder_address"); ?>">
					</input>
				</div>
				<div class="regAddressNumberNumber">
					<input id="inputHousenumber" type="text" autocomplete="address-line2" class="alignCenter" placeholder="<?php echo localize("reg2_address_placeholder_housenumber"); ?>">
					</input>
				</div>
			</div>
			

			<div id="divErrorAddressIsMissing" class="hidden">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("reg2_address_error_address"); ?></div>
			</div> 

			<div id="divErrorHousenumberIsMissing" class="hidden">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("reg2_address_error_housenumber"); ?></div>
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
					3/7
				</div>
				<div class="progress_ind_base progress_ind_1"></div>
				<div class="progress_ind_base progress_ind_2"></div>
				<div class="progress_ind_base progress_ind_3"></div>
				<!-- div class="progress_ind_base progress_ind_4"></div>
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

			var countryName = localStorage.getItem("countryName");
			var countryCode = localStorage.getItem("country");
			if(countryCode && countryCode!="null")
			{
				document.getElementById("dlCountries").value = countryCode;
			}
			if(needsConfirmationNoAdvertising(countryCode)) {
				var elNoAdvertisement = document.getElementById("check_no_advertisement");
				elNoAdvertisement.checked = localStorage.getItem("noAdvertisement");
				$("#divDeclareNoAdvertisement").show();
			} else
				$("#divDeclareNoAdvertisement").hide();

			var city = localStorage.getItem("city");
			document.getElementById("inputCity").value = city;

			var zip = localStorage.getItem("zip");
			document.getElementById("inputZip").value = zip;

			var address = localStorage.getItem("address");
			document.getElementById("inputAddress").value = address;

			var housenumber = localStorage.getItem("housenumber");
			document.getElementById("inputHousenumber").value = housenumber;

			document.getElementById("dlCountries").addEventListener("change", function() {
				var countryCode = document.getElementById("dlCountries").value;
				if(needsConfirmationNoAdvertising(countryCode))
					$("#divDeclareNoAdvertisement").show();
				else
					$("#divDeclareNoAdvertisement").hide();
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

		document.getElementById("buttonBack").addEventListener("click", function() { buttonNavClicked("back"); });
		document.getElementById("buttonContinue").addEventListener("click", function() { buttonNavClicked("continue"); });

		function needsConfirmationNoAdvertising(countryCode) {
			if(countryCode == "AU" || countryCode == "CN") {
				return true;
			} else {
				return false;
			}
		}

		function buttonNavClicked(nameButton) {
			var inputOk = true;

			var showErrors = (nameButton == "continue");
			if(showErrors)
				hideErrorDivs();

			localStorage.removeItem("country"); // code
			localStorage.removeItem("countryName");
			localStorage.removeItem("noAdvertisement")
			trimTextOfInput("dlCountries");

			var countryCode = document.getElementById("dlCountries").value;
			if(countryCode) {
				localStorage.setItem("country", countryCode);
			} else {
				if(showErrors)
					document.getElementById("divErrorCountryIsMissing").classList.remove("hidden");
				inputOk = false;
			}
			if(needsConfirmationNoAdvertising(countryCode)) {
				var elNoAdvertisement = document.getElementById("check_no_advertisement");
				localStorage.setItem("noAdvertisement", elNoAdvertisement.checked);
				if(elNoAdvertisement.checked) {
					// OK	
				} else {
					if(showErrors)
						document.getElementById("divErrorGotAdvertisement").classList.remove("hidden");
					inputOk = false;
				}
			}

			localStorage.removeItem("city");
			trimTextOfInput("inputCity");
			var city = document.getElementById("inputCity").value;
			if(city)
				localStorage.setItem("city", city);
			else {
				if(showErrors)
					document.getElementById("divErrorCityIsMissing").classList.remove("hidden");
				inputOk = false;
			}

			localStorage.removeItem("zip");
			trimTextOfInput("inputZip");
			var zip = document.getElementById("inputZip").value;
			if(zip)
				localStorage.setItem("zip", zip);
			else {
				if(showErrors)
					document.getElementById("divErrorZipIsMissing").classList.remove("hidden");
				inputOk = false;
			}

			localStorage.removeItem("address");
			trimTextOfInput("inputAddress");
			var address = document.getElementById("inputAddress").value;
			if(address)
				localStorage.setItem("address", address);
			else {
				if(showErrors)
					document.getElementById("divErrorAddressIsMissing").classList.remove("hidden");
				inputOk = false;
			}

			localStorage.removeItem("housenumber");
			trimTextOfInput("inputHousenumber");
			var housenumber = document.getElementById("inputHousenumber").value;
			if(housenumber)
				localStorage.setItem("housenumber", housenumber);
			else {
				if(showErrors)
					document.getElementById("divErrorHousenumberIsMissing").classList.remove("hidden");
				inputOk = false;
			}

			if(nameButton == "back")
				document.location = "/registration1.php";
			else {
				if(nameButton == "continue") {
					if(inputOk) {
						document.location = "/registration3.php";
					}
				}
			}
		}

		function countriesListShow() {
			document.getElementById("divListCountries").classList.remove("hidden");
		}

		function countriesListHide() {
			setTimeout(countriesListHide_, 200);
		}

		function countriesListHide_() {
			document.getElementById("divListCountries").classList.add("hidden");
		}

		function countryListClicked(event) {
			console.log(event.target);
			document.getElementById("dlCountries").value = event.target.innerHTML;
			document.getElementById("dlCountries").setAttribute("data-code", event.target.getAttribute("data-code"));
			countriesListHide();
		}

		function hideErrorDivs() {
			document.getElementById("divErrorCountryIsMissing").classList.add("hidden");
			document.getElementById("divErrorGotAdvertisement").classList.add("hidden");
			document.getElementById("divErrorCityIsMissing").classList.add("hidden");
			document.getElementById("divErrorZipIsMissing").classList.add("hidden");
			document.getElementById("divErrorAddressIsMissing").classList.add("hidden");
			document.getElementById("divErrorHousenumberIsMissing").classList.add("hidden");
		}

		function simulateEscapeKey() {
			var keyboardEvent = document.createEvent("KeyboardEvent");
			var initMethod = typeof keyboardEvent.initKeyboardEvent !== 'undefined' ? "initKeyboardEvent" : "initKeyEvent";

			keyboardEvent[initMethod](
				"keydown", // event type: keydown, keyup, keypress
				true,      // bubbles
				false,      // cancelable
				window,    // view: should be window
				false,     // ctrlKey
				false,     // altKey
				false,     // shiftKey
				false,     // metaKey
				27,        // keyCode: unsigned long - the virtual key code, else 0
				0          // charCode: unsigned long - the Unicode character associated with the depressed key, else 0
			);
			document.dispatchEvent(keyboardEvent);
		}
	</script>
</body>