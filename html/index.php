<?php
try {
	include("configuration.php");
	include("translate.php");
} catch (Exception $e) {
	echo "Exception: " . $e->getMessage();
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
		<!-- div class="panel_logo col-lg-6 col-md-6 col-sm-12 col-xs-12" -->
		<div class="reg_container_logo">
			<div class="panel_logo_logo">
				<img src='Images/logo1.png' width=180 height=60></img>
			</div>
			<div class="panel_logo_text">
				<?php echo localize("index_logo_text"); ?>
			</div>
			<div id="indexStatsContainer" class="hidden">
				<div class="">
					
					<div id="indexStats1" class="">
						<div >
							<div class="indexStatsSymbolBackground">
							</div>
							<div class="indexStatsSymbol">
								<img src='Images/profile.jpg' width=18 height=18></img>
							</div>
						</div>
						<div class="indexStatsText">
							<div id="divStatsMembers" class="indexStatsTextNumber beige">
								&nbsp;
							</div>
							<div class="colorPlaceholder">
								<?php echo localize("reg_stats_members"); ?>
							</div>
						</div>
					</div>


					<div id="indexStats2" class="">
						<div >
							<div class="indexStatsSymbolBackground">
							</div>
							<div class="indexStatsSymbol2">
								$
							</div>
						</div>
						<div class="indexStatsText">
							<div class="indexStatsTextNumber beige">
								999.000.000
							</div>
							<div class="colorPlaceholder">
								<?php echo localize("reg_stats_volume"); ?>
							</div>
						</div>
					</div>

				</div>
			</div>		
		</div>

		<!-- div class="register_panel_content col-lg-6 col-md-6 col-sm-12 col-xs-12" -->
		<div class="reg_container_content">
			<div class="panel_logo_logo_input">
				<img src='Images/logo1.png' width=180 height=60></img>
			</div>

			<div class="panel_reg_fill_top">
			</div>

			<div>
				<input id="buttonSignUp" type="button" class="button_border" value="<?php echo localize("index_button_sign_up"); ?>"></input>
				<input id="buttonSignIn" type="button" class="button_no_border" value="<?php echo localize("index_button_sign_in"); ?>"></input>
			</div>

			<div class="verticalSpacer"></div>
			<div class="verticalSpacer"></div>

			<div class="textPageIndexTop">
				<?php echo localize("index_enter_code"); ?>
			</div>

			<div class="verticalSpacer"></div>

			<!-- input class="inputCode" type="text" placeholder="Enter your Code" pattern="[0-9]{10}" -->
			<input id="inputTemporaryCode" class="" type="text" name="temporaryCode" value="<?php echo $temporaryCode; ?>" placeholder="<?php echo localize("index_placeholder_code"); ?>">
			</input>

			<div id="divErrorCodeIsMissing" class="hidden">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("index_error_code_missing"); ?></div>
			</div>
			<div id="divErrorCodeUnknown" class="hidden">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("index_error_code_unknown"); ?></div>
			</div>
			<div id="divErrorCodeNotValid" class="hidden">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("index_error_code_expired"); ?></div>
			</div>
			<div id="divErrorCodeWasValidated" class="hidden">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("index_error_code_validated"); ?></div>
			</div>
			<div id="divErrorCodeInUse" class="hidden">
				<div class="verticalSpacer"></div>
				<div class="warning-text"><?php echo localize("index_error_code_used"); ?></div>
			</div>

			<div class="verticalSpacer"></div>

			<div class="">
				<input id="buttonSubmitCode" type="button" class="button_filled" value="<?php echo localize("index_start"); ?>"></input>
			</div>

			<div class="verticalSpacer"></div>
			<div class="verticalSpacer"></div>

			<input type='hidden' name='startRegistration' value='true' />

			<div class="textPageIndexBottom">
				<?php echo localize("index_text"); ?>
			</div>

			<div class="panel_fill_vertical">
			</div>

		</div>
	</div>

	<script>
		window.onload = function() {
			localStorage.clear();
			hideErrorDivs();

			/* set viewport to absolute pixels to prevent mobile keyboard from resizing screen */
			setTimeout(function() {
				var viewheight = $(window).height();
				var viewwidth = $(window).width();
				var viewport = $("meta[name=viewport]");
				viewport.attr("content", "height=" + viewheight + "px, width=" +
					viewwidth + "px, initial-scale=1.0");
			}, 300);
		}

		function hideErrorDivs() {
			document.getElementById("divErrorCodeIsMissing").classList.add("hidden");
			document.getElementById("divErrorCodeUnknown").classList.add("hidden");
			document.getElementById("divErrorCodeNotValid").classList.add("hidden");
			document.getElementById("divErrorCodeWasValidated").classList.add("hidden");
			document.getElementById("divErrorCodeInUse").classList.add("hidden");
		}

		document.getElementById("buttonSignIn").addEventListener("click", function(e) {
			document.location = "/bo_main.php";
		});

		document.getElementById("inputTemporaryCode").addEventListener("keydown", function(e) {
			if (e.keyCode === 13) {
				temporaryCodeSubmit();
			} else {
				hideErrorDivs();
			}
		});

		document.getElementById("buttonSubmitCode").addEventListener("click", function(e) {
			temporaryCodeSubmit();
		})

		function temporaryCodeSubmit() {
			hideErrorDivs();

			var temporaryCode = document.getElementById("inputTemporaryCode").value.trim();

			if (!temporaryCode) {
				document.getElementById("divErrorCodeIsMissing").classList.remove("hidden");
				return;
			}

			$.ajax("ajax_check_temporary_code.php", {
					data: {
						temporaryCode: temporaryCode
					},
					dataType: "json"
				})
				.done(function(data) {
					console.log(data)
					if (data.code == 1) {
						localStorage.setItem("temporaryCode", temporaryCode);
						localStorage.setItem("temporaryCodeCheckDateTime", data.date_time_checked);
						document.location = "/registration0.php";
					} else if (data.code == -7) {
						document.getElementById("divErrorCodeWasValidated").classList.remove("hidden");
					} else if (data.code == -3) {
						document.getElementById("divErrorCodeInUse").classList.remove("hidden");
					} else if (data.code == -4) {
						document.getElementById("divErrorCodeNotValid").classList.remove("hidden");
					} else if (data.code == -5) {
						document.getElementById("divErrorCodeUnknown").classList.remove("hidden");
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
