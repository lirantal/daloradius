<?php
/*********************************************************************
* Name: attributes.php
* Author: Liran tal <liran.tal@gmail.com>
* 
* This file is used by the management page (edit user) 
* and it's general purpose is to return the table string
* for a given attribute name
* 
* @param $attribute	The attribute name, Session-Timeout for example
* @return $table		The table name, either radcheck or radreply
*********************************************************************/

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
