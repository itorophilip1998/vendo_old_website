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
	<div id="divToBlur" class="reg_container_all">
		<div class="reg_container_logo">
			<div class="panel_logo_logo">
				<img src='Images/logo1.png' width=180 height=60></img>
			</div>
			<div class="panel_logo_text">
				<?php echo localize("reg0_logo_text"); ?>
			</div>
		</div>

		<div class="reg_container_content">
			<div class="panel_logo_logo_input">
				<img src='Images/logo1.png' width=180 height=60></img>
			</div>

			<div class="panel_reg_fill_top">
			</div>

			<div class="verticalSpacer"></div>
		

			<div class="verticalSpacerS"></div>
			<select id="selectLanguage" class="pointer">
				<option value="" disabled selected class="colorPlaceholder placeholder"><?php echo localize("reg0_choose_language"); ?></option>
				<option value="en" class="colorText">English</option>
				<option value="de" class="colorText">Deutsch</option>
				<option value="fr" class="colorText">Français</option>
			</select>

			<div class="verticalSpacerS"></div>

			<div class="panel_fill_vertical">
			</div>
			
			<div class="">
				<input id="buttonContinue" type="button" class="button_filled float_right" value="<?php echo localize("general_reg_next"); ?>"></input>
			</div>
			
			<div class="verticalSpacer"></div>

			<div class="progress_ind_all">
				<div class="progress_text">
					1/7
				</div>
				<div class="progress_ind_base progress_ind_1"></div>
				<!--div class="progress_ind_base progress_ind_2"></div>
				<div class="progress_ind_base progress_ind_3"></div>
				<div class="progress_ind_base progress_ind_4"></div>
				<div class="progress_ind_base progress_ind_5"></div>
				<div class="progress_ind_base progress_ind_6"></div>
				<div class="progress_ind_base progress_ind_7"></div-->
			</div>
			
			<div class="panel_reg_fill_bottom">
			</div>
		</div>
	</div>

	<div id="divOverlayVideo" style="display: none">
		<div id="divOverlayVideoCloseX" class="pointer"><i class="fas fa-times"></i></div>
		<div id="divVideo"></div>
		<div id="divPreviewImage">
			<img src="./Images/preview_welcome_video.jpg" id="imgPreviewImage"></img>
			<img src="./Images/loading.gif" id="imgPreviewLoading"></img>
		</div>
	</div>

	<script>
		window.onload = function() {
			var temporaryCode = localStorage.getItem("temporaryCode");
			if(!temporaryCode) {
				document.location = "/index.php";
				return;
			}

			document.getElementById("buttonContinue").addEventListener("click", buttonContinueClicked);

			var language = localStorage.getItem("language");
			if(language) {
				document.getElementById("selectLanguage").value = language;
				// document.getElementById("selectLanguage").classList.add("colorText");
			}

			document.getElementById("selectLanguage").addEventListener("change", function() {
				var language = document.getElementById("selectLanguage").value;
			});
		};

		function buttonContinueClicked(event) {
			var inputOk = false;

			localStorage.removeItem("language");
			//trimTextOfInput("selectCountry");
			var language = document.getElementById("selectLanguage").value;
			if(language)	
			{
				//if language is french
				if (language == 'fr')
				{
					//remove french from the options
					$("#selectLanguage option[value='fr']").remove();

					//set selection back to placeholder, and change placeholder text to "choose display language"
					$("#selectLanguage option.placeholder").html('<?php echo localize("reg0_choose_language"); ?>');
					$("#selectLanguage").val("");
				}
				else{
					localStorage.setItem("language", language);
					inputOk = true;
				}
			}


			if(inputOk) {
				document.location = "/registration1.php?locale=" + language;
			}
		}

	</script>
</body>
