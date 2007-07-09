<?php

	function checkTables($attribute) {
	
		$table = "radcheck";

		switch ($attribute) {
			case "Session-Timeout":
				$table = "radreply";
				break;
			case "Idle-Timeout":
				$table = "radreply";
				break;
			case "WISPr-Redirection-URL":
				$table = "radreply";
				break;
			case "WISPr-Bandwidth-Max-Up":
				$table = "radreply";
				break;
			case "WISPr-Bandwidth-Max-Down":
				$table = "radreply";
				break;
			case "WISPr-Session-Terminate-Time":
				$table = "radreply";
				break;
		}

		return $table;


	}



?>
