<?php

	include_once('library/config_read.php');
	
	switch($configValues['CONFIG_LANG']) {
	
		case "en":
			include ("lang/en.php");
			break;
		case "ru":
			include ("lang/ru.php");
			break;
		default:
			include ("lang/en.php");
			break;
	}

?>
