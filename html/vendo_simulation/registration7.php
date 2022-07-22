<?php
try {
	include("utils.php");
	include("translate.php");
	include("configuration.php");

	$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	include 'bo_read_user_logged_in.php';
	// is logged in

} catch (Exception $e) {
	$msg = "exception: " . $e->getMessage();
	echo $msg;
	error_log($msg);
	die();
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
		<div class="reg_container_logo">
			<div class="panel_logo_logo">
				<img src='Images/logo1.png' width=180 height=60></img>
			</div>
			<div class="panel_logo_text">
				<?php
					$textTemplate = localize("reg7_logo_text");
					$textLogo = str_replace("[[GivenName]]", $user["given_name"], $textTemplate);
					echo $textLogo;
				?>
			</div>
		</div>

		<div class="reg_container_content">
			<div class="panel_logo_logo_input">
				<img src='Images/logo1.png' width=180 height=60></img>
			</div>

			<div class="panel_reg_fill_top">
			</div>

			<div class="verticalSpacer"></div>
			
			<div class="reg_finish_title">
				<?php
					$nameAccount = strtoupper(getNameTradingAccount($user["trading_account"]));
					$textTemplate = localize("reg7_finish_title");
					$textTitle = str_replace("[[NameAccount]]", $nameAccount, $textTemplate);
					echo $textTitle;
				?>
			</div>

			<div class="verticalSpacer"></div>

			<div class="reg_finish_text">
				<?php echo localize("reg7_finish_text") ?>
			</div>

			<div class="verticalSpacer"></div>
			<div class="verticalSpacer"></div>
			<div class="verticalSpacer"></div>

			<div class="">
				<input id="buttonDashboard" type="button" class="button_filled" value="<?php echo localize("reg7_button_dashboard") ?>"></input>
			</div>
			
			<div class="verticalSpacer"></div>

			<div class="">
				<input id="buttonProfile" type="button" class="button_border" value="<?php echo localize("reg7_button_profile") ?>"></input>
			</div>

			<div class="panel_fill_vertical">
			</div>        
		</div>
	</div>

    <script>
        window.onload = function() {
            document.getElementById("buttonDashboard").addEventListener("click", function(event) {
                document.location = "/bo_main.php";
            });
			document.getElementById("buttonProfile").addEventListener("click", function(event) {
                document.location = "/bo_profile.php";
            });
        }
    </script>
</body>