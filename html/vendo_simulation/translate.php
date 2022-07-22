<?php
	define('LANGUAGE_DEFAULT','en');

	function localize($phrase, $language = null, $relative_path = "") {
		
		static $translations = NULL;
		static $locale = NULL;

		if(session_status() == PHP_SESSION_NONE){
			session_start();
        }
        
		if($language)
		{
			$_SESSION["locale"]=$language;
		}
		else{
			$language = $_SESSION["locale"];
		}
						
		if (is_null($translations) || $locale != $language) {
			
			//echo ("reload translations: ".$locale."!=".$_SESSION["locale"]."<BR/>");
			
			if(isset($_REQUEST["locale"])) {
                $loc = $_REQUEST["locale"];
				$_SESSION["locale"] = $loc;				
			}
			else if(isset($_SESSION["locale"]))
			{
				$loc = $_SESSION["locale"];
			}
			else
			{
				$loc = $language;
			}

			$lang_file = $relative_path . "lang_".$loc.'.json';

			if (!file_exists($lang_file)) {
				// echo $lang_file." does not exist<br>";
				$lang_file = $relative_path . "lang_".LANGUAGE_DEFAULT.".json";
				$_SESSION["locale"] = $language = LANGUAGE_DEFAULT;
			} else {
				// echo $lang_file." does exist<br>";
			}

			$lang_file_content = file_get_contents($lang_file);

			$translations = json_decode($lang_file_content, true);
			
			/*
			switch(json_last_error()) {
				case JSON_ERROR_NONE:
					echo ' - Keine Fehler';
				break;
				case JSON_ERROR_DEPTH:
					echo ' - Maximale Stacktiefe überschritten';
				break;
				case JSON_ERROR_STATE_MISMATCH:
					echo ' - Unterlauf oder Nichtübereinstimmung der Modi';
				break;
				case JSON_ERROR_CTRL_CHAR:
					echo ' - Unerwartetes Steuerzeichen gefunden';
				break;
				case JSON_ERROR_SYNTAX:
					echo ' - Syntaxfehler, ungültiges JSON';
				break;
				case JSON_ERROR_UTF8:
					echo ' - Missgestaltete UTF-8 Zeichen, möglicherweise fehlerhaft kodiert';
				break;
				default:
					echo ' - Unbekannter Fehler';
				break;
			}
			*/
	
			$locale=$_SESSION["locale"];
		} else {
			// echo "no reload translations <BR/>";
		}
		
		if(array_key_exists($phrase, $translations))
			return $translations[$phrase];
		else
			return $phrase;
	}
?>