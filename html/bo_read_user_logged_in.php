<?php
    include_once("db.php");

	if(session_status() == PHP_SESSION_NONE){
		session_start();
	}
	$loggedInAsUserId = $_SESSION["loggedInAsUserId"];
			
	if(!$loggedInAsUserId) {
		unsetUser();
		$destination = $_SERVER['REQUEST_URI'];
		//echo "not logged in";
		
		header("Location:bo_login.php?destination=".urlencode($destination));
		die();
	} else {
		$user = readUser($pdo, $loggedInAsUserId);

		//print_r($user);
		if(!$user) {
			unsetUser();
			header("Location:bo_login.php?destination=".urlencode($destination));
			die();
		}

		$user["dateOfBirthTime"] = strtotime($user["date_of_birth"]);

		$_SESSION["locale"] = $user["language"];
		
		//if(!$user["is_member"] && !$user["membertype"]==1) {
		//if(!$user["is_member"]) {
		//	echo(Localized::localize("bo_not_a_member", $user["language_backoffice"]));
		//	unsetUser();
		//	die();
		//}

		/*
		if(!$user["reg_complete"]) {
			echo("Registration incomplete.");
			unsetUser();
			die();
		}
		*/
	}
    
    /*
	function localize($phrase) {
		global $user;
		return Localized::localize($phrase, $user["language_backoffice"]);
    }
    */
	
	function unsetUser(){
		unset($_SESSION['loggedInAsUserId']);
		unset($GLOBALS['$loggedInAsUserId']);	
		unset($GLOBALS['$user']);	
	}
?>