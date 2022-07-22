<?php
	try {
		require_once("configuration.php");	
		require_once("utils.php");
		require_once("translate.php");


		$pdo = new PDO(DB_DSN, DB_USER, DB_PASS,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); 
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

		$email = trim(getRequest("email"));
		$destination = getRequest("destination");
		
		$sql = "SELECT * FROM User ".
					"WHERE ".
						"email=:email;";
		$sth = $pdo->prepare($sql);
		$sth->bindParam(':email',$email);

		if (!$sth->execute())
		{
			$msg = "Database Error: ".$sth->errorInfo()[2].PHP_EOL."Original SQL: $sql";
			error_log ($msg);
			die();
		}
		
		if($sth->rowCount()==0) {
			$userNotFound = true;
		} else {
			$user = $sth->fetch(PDO::FETCH_ASSOC);
							
            //
            $newPassword = randomPassword();
			
			$success = changeUserPassword($email, $newPassword, $user['id']);
                    
            if($success) {
                // send email
				$mailSubject = localize('bo_reset_password_mail_subject', $user['language']); //"Vendo backoffice password reset";
				//todo: get mail body from template
				$mailBody = localize('bo_reset_password_mail_body', $user['language']); //"Your password was reset to '[[NewPassword]]'.";
				/*$mailBody .= localize('general_email_signature', $user['language']);*/
                $mailBody = str_replace("[[NewPassword]]", $newPassword, $mailBody);
                    
                $fromReplyTo = FROM_MAIL_GENERAL;
                                
                $sentEMail = sendEmail($fromReplyTo, $email, $mailSubject, $mailBody);
            }
		}

		$ajaxCall = $_POST['ajaxResetPassword'];
		if(isset($ajaxCall)) {
			if($ajaxCall == "true") {
				echo json_encode([
					'code' => 200,
					'message' => localize('bo_profile_reset_password_error', $user['language'])
				]);
				die();
			}
		}
			
	} catch (Exception $e) {
		$msg="exception: ".$e->getMessage()."; ".$e->getFile()."; Line: ".$e->getLine();
        error_log ($msg);
    }
		
?>

<html>
<head>
	
		
	<?php
		echo file_get_contents("bo_head.html");		
	?>		
</head>
<body>
			  
	<div class="container-all col-lg-12 col-md-12 col-sm-12 col-xs-12">	
		<div class="container-article">

			<h2>Vendo backoffice password reset</h2>
		
			<?php
				echo localize('bo_reset_password_done', $user['language']);
				
				echo "<br/>";
				echo "<a href=\"/bo_login.php?email=".urlencode($email)."&destination=".urlencode($destination)."\">[login]</a>";
				
			?>
	
		</div>
	</div>
	
</body>