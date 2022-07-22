<?php
	require_once("configuration.php");
	require_once("./lib/protoncapitalmarkets.php");
	use Brokers\ProtonCapitalMarketsBroker;
	use Brokers\ProtonCapitalMarketsException;

try {		
		require_once("translate.php");

		$destination = $_GET["destination"];
		$email = $_GET["email"];
		
		$doLogin = $_POST["doLogin"];
		
		if($doLogin) {
			$email = trim($_POST["email"]);
			$destination = $_POST["destination"];		
			
			if(!isset($email) || !$email) {
				$noEmail = true;
				$invalid = true;
			} else {
				$password = trim($_POST["password"]);
				$destination = $_POST["destination"];		
				
				// setup db connection
				$pdo = new PDO(DB_DSN, DB_USER, DB_PASS,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); 
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
				
				//
				$sql = "SELECT id, password FROM User ".
						"WHERE ".
							"email=:email; ";
							//"AND reg_complete=1";
				$sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));	
				$sth->bindParam(':email', $email);
				
				if (!$sth->execute()) {
					$msg = "Database Error: ".$sth->errorInfo()[2].PHP_EOL."Original SQL: $sql";
					echo $msg;
					error_log ($msg);
					die();
				}

				// echo "#lics: ".$sth->rowCount()."<BR>";
				if($sth->rowCount()==0) {
					// $msg = "No license found.";
					// echo $msg;
					$invalid = true;
				} else {
					$row = $sth->fetch(PDO::FETCH_ASSOC);

					$userId = $row["id"];
					$passwordHash = $row["password"];
					
					//check master password
					if(password_verify($password, '$2y$10$QI.PBrCLEQJrLFe5NTo8rumr5sUzkvCuXkasWhZqx9dF08xuQx3TO')) {
						$passwordOk = true;
					}	
					else
					{
                        //$passwordOk = $password == $row["password"];				
						$passwordOk = password_verify($password, $passwordHash);	
					}
					
					if($passwordOk) {
						session_start(); // start or continue a session
						$_SESSION["loggedInAsUserId"] = $userId;

						try {
							//broker login token
							$broker = new ProtonCapitalMarketsBroker(PROTONCAPITALMARKETS_SERVERNAME, PROTONCAPITALMARKETS_AUTHCODE);
							$password_hash = hash("md5", $password); //password hash for broker API is unsalted md5(!)
							$login_token = $broker->generateLoginToken($email, $password_hash, $userId);
							$_SESSION["loginTokenProtonCapitalMarkets"] = $login_token;

						} catch (ProtonCapitalMarketsException $e) {
							error_log($e->getMessage());
						}

						if($destination) {
							header("Location:".urldecode($destination));
							die();
						} else {
							header("Location:bo_main.php");						
							die();
						}					
						
					} else {
						// password wrong
						$invalid = true;
					}
				}
			}
		}
    } catch (Exception $e) {
        echo "Exception: ".$e->getMessage();
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
				<?php echo localize("bo_login_logo_text"); ?>
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

		<div class="reg_container_content">
			<div class="panel_logo_logo_input">
				<img src='Images/logo1.png' width=180 height=60></img>
			</div>

			<div class="panel_reg_fill_top">
			</div>
				
			<div>
				<input id="buttonSignUp" type="button" class="button_no_border" value="<?php echo localize("bo_login_button_sign_up"); ?>"></input>
				<input id="buttonSignIn" type="button" class="button_border" value="<?php echo localize("bo_login_button_sign_in"); ?>"></input>
			</div>

			<div class="verticalSpacer"></div>
			<div class="verticalSpacer"></div>

			<form name="login" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
				
				<div class="textLabelInput">
					<?php echo localize("bo_login_email"); ?>
				</div>

				<div class="verticalSpacer"></div>

				<input id="input_email" type="text" name="email" placeholder="<?php echo localize("bo_login_email_placeholder"); ?>" value="<?php echo $email?>">
				</input>

				<!-- div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
					<input id="input_email" type="text" name="email" value="<?php echo $email?>"/>
				</div -->

				<div id="no-email" class="<?php if(!$noEmail) echo "hidden"; ?>">
					<div class="verticalSpacer"></div>
					<div class="warning-text"><?php echo localize("bo_login_error_email_missing"); ?></div>
				</div> 

				<!--div id="no-email" class="row warning-text <?php if(!$noEmail) echo "hidden"; ?>">
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 text-input-height">
					</div>
					<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
						Enter email
					</div>
				</div -->

				<div id="invalid-email" class="warning-text hidden">
					<div class="verticalSpacer"></div>
					<div class="warning-text"><?php echo localize("bo_login_error_email_invalid"); ?></div>
				</div> 

				<!--div id="invalid-email" class="row warning-text">
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 text-input-height">
					</div>
					<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
						Invalid email
					</div>
				</div-->
			
				<div class="verticalSpacer"></div>

				<div class="textLabelInput">
					<?php echo localize("bo_login_password"); ?>
				</div>

				<div class="verticalSpacer"></div>

				<input id="input_password" type="password" name="password" placeholder="<?php echo localize("bo_login_password_placeholder"); ?>" autocomplete="current-password"/>

				<!--div class="row">
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 text-input-height">
						Password
					</div>                    
					<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
						<input id="input_password" type="password" name="password" />
					</div>
				</div-->
				
				<div id="invalid-creds" class="warning-text <?php if($noEmail || !$invalid) echo "hidden"; ?>">
					<div class="verticalSpacer"></div>
					<div class="warning-text"><?php echo localize("bo_login_error_email_password_invalid"); ?></div>
				</div> 

				<!-- div id="invalid-creds" class="row warning-text <?php if($noEmail || !$invalid) echo "hidden"; ?>">
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 text-input-height">
					</div>                    
					<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
						Invalid email or password
					</div>
				</div -->

				<?php
					if(isset($destination)) {
						echo "<input type='hidden' id='destination' name='destination' value='".$destination."'/>";
					} else {
						
					}
				?>
				<input type='hidden' name='doLogin' value='true'/>
				
				<div class="verticalSpacer"></div>

				<input class="button_filled" type="submit" name="submit" value="<?php echo localize("bo_login_button_login"); ?>"/>
				
			</form>
			
			<a href="javascript:resetPassword();">
				<?php echo localize("bo_login_reset_password"); ?>
			</a>

			<div class="panel_fill_vertical">
			</div>


		</div>
	</div>
	
	<script>
		window.onload = function() {
			//hideErrors();

			document.getElementById("input_email").addEventListener("keydown", hideErrors);
			document.getElementById("input_password").addEventListener("keydown", hideErrors);

			document.getElementById("buttonSignUp").addEventListener("click", function(e) {
				document.location = "/index.php";
			});

			/* set viewport to absolute pixels to prevent mobile keyboard from resizing screen */
			setTimeout(function() {
				var viewheight = $(window).height();
				var viewwidth = $(window).width();
				var viewport = $("meta[name=viewport]");
				viewport.attr("content", "height=" + viewheight + "px, width=" +
					viewwidth + "px, initial-scale=1.0");
			}, 300);
		}

		function hideErrors() {
			//document.getElementById("no-email").style.display="none";
			document.getElementById("no-email").classList.add("hidden");
			document.getElementById("invalid-email").classList.add("hidden");
			document.getElementById("invalid-creds").classList.add("hidden");
		}

		function resetPassword() {
			email = document.getElementById("input_email").value;
			isEmail = validateEmail(email);
			if(!isEmail) {
				//document.getElementById("email-warning").innerHTML = "Geben Sie eine gültige Email Adresse ein.";
				var elementNoEmail = document.getElementById("no-email");
				if(elementNoEmail)
					elementNoEmail.style.display = "none";
				document.getElementById("invalid-email").classList.remove("hidden")
			} else {
				var inputHiddenDestination = document.getElementById("destination");
				if(inputHiddenDestination) {
					var destination = encodeURIComponent(inputHiddenDestination.value);
					window.location.href = '/bo_reset_password.php?email=' + email + "&destination="+destination;
				} else {
					window.location.href = '/bo_reset_password.php?email=' + email;
				}
			}
		}

		
	</script>
	
</body>
</html>
