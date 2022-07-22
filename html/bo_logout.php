<?php
	session_start();
	//TODO: wating for ProtonCapitalMarkets API Devs to provide API Method to invalidate login token
	//when API provides function -> call invalidate login token before unsetting $_SESSION["loginTokenProtonCapitalMarkets"]
	session_destroy();
	header("Location:/bo_login.php");
?>