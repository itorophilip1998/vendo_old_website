<?php
	include("translate.php");
	include("configuration.php");

	$registrationFeeAmount = number_format(REGISTRATION_FEE, 2, '.', ',');
	$registrationFee = true;

	$AccountType = 0;
	$AccountFee = 0;
	$amountToPay = 0;

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
				<?php echo localize("reg6_logo_text"); ?>
			</div>
		</div>

		<div class="reg_container_content">
			<div class="panel_logo_logo_input">
				<img src='Images/logo1.png' width=180 height=60></img>
			</div>

			<?php
				include("./choose_payment_method.php");
			?>

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
			if (!tradingAccount) {
				document.location = "/registration5.php"
			}

			var registrationFee = <?php echo REGISTRATION_FEE ?>;
			var textRegistrationFee = numeral(registrationFee).format('0,0.00');
			document.getElementById("table_cell_registration_fee").innerHTML = "$ " + textRegistrationFee;

			var accountName = "";
			var accountAmount = 0;
			var accountFee = 0;
			if (tradingAccount == 1) {
				accountName = "<?php echo ACCOUNT_1_NAME ?>";
				accountFee = <?php echo ACCOUNT_1_FEE ?>;
			} else if (tradingAccount == 2) {
				accountName = "<?php echo ACCOUNT_2_NAME ?>";
				accountFee = <?php echo ACCOUNT_2_FEE ?>;
			} else if (tradingAccount == 3) {
				accountName = "<?php echo ACCOUNT_3_NAME ?>";
				accountFee = <?php echo ACCOUNT_3_FEE ?>;
			} else if (tradingAccount == 4) {
				accountName = "<?php echo ACCOUNT_4_NAME ?>";
				accountFee = <?php echo ACCOUNT_4_FEE ?>;
			}

			var textAccess = "<?php echo localize("reg6_table_access") ?>";
			textAccess = textAccess.replace("[[NameAccount]]", accountName);
			document.getElementById("table_cell_account_type").innerHTML = textAccess;
			var textFee = numeral(accountFee).format('0,0.00');
			document.getElementById("table_cell_account_fee").innerHTML = "$ " + textFee;
			var sum = accountFee + registrationFee;
			var textSum = numeral(sum).format('0,0.00');
			document.getElementById("table_cell_sum").innerHTML = "$ " + textSum;

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
			document.getElementById("divErrorNoPaymentMethod").classList.add("hidden");
		}

		function buttonNavClicked(nameButton) {
			var inputOk = true;

			var showErrors = (nameButton == "continue");
			if (showErrors)
				hideErrorDivs();

			localStorage.removeItem("paymentMethod");
			var iPaymentMethod = 0;
			var radios = document.getElementsByName("radio_payment_method");
			for (var iR = 0; iR < radios.length; iR++) {
				if (radios[iR].checked) {
					iPaymentMethod = parseInt(radios[iR].value);
					break;
				}
			}
			if (iPaymentMethod > 0)
			{
				localStorage.setItem("paymentMethod", iPaymentMethod);
			}
			else {
				if (showErrors)
					document.getElementById("divErrorNoPaymentMethod").classList.remove("hidden");
				inputOk = false;
			}

			localStorage.removeItem("paymentCurrency");
			var iPaymentCurrency = '';

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
			else{
				iPaymentCurrency = "USDT.ERC20";
			}
			if (iPaymentCurrency)
			{
				localStorage.setItem("paymentCurrency", iPaymentCurrency);
			}

			if (nameButton == "back")
				document.location = "/registration5.php";
			else {
				if(nameButton == "continue") {
					if(inputOk)
						registerUser();
				}
			}
		}

		function post(url, parameters) {
			var form = $('<form></form>');

			form.attr("method", "post");
			form.attr("action", url);

			$.each(parameters, function(key, value) {
				var field = $('<input></input>');

				field.attr("type", "hidden");
				field.attr("name", key);
				field.attr("value", value);

				form.append(field);
			});

			// The form needs to be a part of the document in
			// order for us to be able to submit it.
			$(document.body).append(form);
			form.submit();
		}

		function registerUser() {
			var temporaryCode = localStorage.getItem("temporaryCode");
			if(!temporaryCode) {
				document.location = "/index.php";
				return;
			}
			var temporaryCodeCheckDateTime = localStorage.getItem("temporaryCodeCheckDateTime");
			if(!temporaryCodeCheckDateTime) {
				document.location = "/index.php";
				return;
			}

			var goBack = false;

			// check registration page
			var language = localStorage.getItem("language");
			if (!language)
				goBack = true;
			if (goBack) {
				document.location = "/registration0.php";
				return;
			}

			// check registration page
			var sex = localStorage.getItem("sex");
			console.log(sex)
			if (!["0", "1"].includes(sex)) {
				goBack = true;
			}
			var givenName = localStorage.getItem("givenName");
			if (!givenName) {
				goBack = true;
			}
			var surName = localStorage.getItem("surName");
			if (!surName) {
				goBack = true;
			}
			var dateOfBirthText = localStorage.getItem("dateOfBirth");
			if (!dateOfBirthText) {
				goBack = true;
			}
			if (goBack) {
				document.location = "/registration1.php";
				return;
			}

			// check registration page
			var country = localStorage.getItem("country");
			if (!country)
				goBack = true;
			var city = localStorage.getItem("city");
			if (!city)
				goBack = true;
			var zip = localStorage.getItem("zip");
			if (!zip)
				goBack = true;
			var address = localStorage.getItem("address");
			if (!address)
				goBack = true;
			var housenumber = localStorage.getItem("housenumber");
			if (!housenumber)
				goBack = true;			
			if (goBack) {
				document.location = "/registration2.php";
				return;
			}

			// check phonecode
			var countryCodeForPhoneCode = localStorage.getItem("phoneCodeSelect");
			if (!countryCodeForPhoneCode) {
				document.location = "/registration3.php";
				return;
			}

			// check registration page
			var mobileNumber = localStorage.getItem("mobileNumber");
			if (!mobileNumber)
				goBack = true;
			var email = localStorage.getItem("email");
			if (!email)
				goBack = true;
			if (goBack) {
				document.location = "/registration3.php";
				return;
			}

			// check registration page
			var password = localStorage.getItem("password");
			if (!password)
				goBack = true;
			var strAcceptedConditions = localStorage.getItem("acceptedConditions");
			if (strAcceptedConditions != "true")
				goBack = true;
			if (goBack) {
				document.location = "/registration4.php";
				return;
			}

			// check registration page
			var tradingAccount = localStorage.getItem("tradingAccount");
			if (!tradingAccount || tradingAccount < 1 || tradingAccount > 4)
				goBack = true;
			if (goBack) {
				document.location = "/registration5.php";
				return;
			}

			var paymentMethod = localStorage.getItem("paymentMethod");

			var paymentCurrency = localStorage.getItem("paymentCurrency");

			// call server
			$.ajax("ajax_register_user.php", {
					data: {
						temporaryCode: temporaryCode,
						temporaryCodeCheckDateTime: temporaryCodeCheckDateTime,

						language: language,

						sex: sex,
						givenName: givenName,
						surName: surName,
						//givenName: "",
						//surName: fullName,
						dateOfBirthText: dateOfBirthText,

						country: country,
						city: city,
						zip: zip,
						street: address,
						housenumber: housenumber,

						mobileNumber: mobileNumber,
						email: email,

						password: password,

						tradingAccount: tradingAccount,

						paymentMethod: paymentMethod,

						currency: paymentCurrency,

						countryCodeForPhoneCode: countryCodeForPhoneCode
					},
					dataType: "json"
				})
				.done(function(data) {
					if (data.code == 1) {
						console.log(data)
						localStorage.setItem("userId", data.userId);
						
						post('registration_payment.php', {
							userId: data.userId,
							temporaryCode: temporaryCode,
							temporaryCodeCheckDateTime: temporaryCodeCheckDateTime,

							language: language,

							sex: sex,
							givenName: givenName,
							surName: surName,
							//givenName: "",
							//surName: fullName,
							dateOfBirthText: dateOfBirthText,

							country: country,
							city: city,
							zip: zip,
							street: address,
							housenumber: housenumber,

							countryCodeForPhoneCode: countryCodeForPhoneCode,
							mobileNumber: mobileNumber,
							email: email,

							password: password,

							tradingAccount: tradingAccount,

							paymentMethod: paymentMethod,

							currency: paymentCurrency
						});

					} else if (data.code == -10) {
						document.location = "/registration3.php?emailInUse=1";
						return;
					} else if (data.code < 0) {
						toastr['error'](data.message);
						return;
					} else {
						console.log(data)
					}
				})
				.fail(function(xhr, status, error) {
					// console.log(xhr);
					// console.log(status);
					// console.log(error);
				})
				.always(function(xhr, status, error) {})		
		}
	</script>
</body>