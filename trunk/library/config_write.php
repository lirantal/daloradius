<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 * Description:
 * 		writes configuration information from the $configValues array to daloradius.conf
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

$configFile = dirname(__FILE__).'/daloradius.conf.php';
$date = date("D M j G:i:s T Y");

$fp = fopen($configFile, "w");
if ($fp) {
	fwrite($fp, 
		"<?php\n".
		"/*\n".
		" *********************************************************************************************************\n".
		" * daloRADIUS - RADIUS Web Platform\n".
		" * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.\n".
		" *\n".
		" * This program is free software; you can redistribute it and/or\n".
		" * modify it under the terms of the GNU General Public License\n".
		" * as published by the Free Software Foundation; either version 2\n".
		" * of the License, or (at your option) any later version.\n".
		" *\n".
		" * You should have received a copy of the GNU General Public License\n".
		" * along with this program; if not, write to the Free Software\n".
		" * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.\n".
		" *\n".
		" *********************************************************************************************************\n".
		" * Description:\n".
		" *              daloRADIUS Configuration File\n".
		" *\n".
		" * Modification Date:\n".
		" *              $date\n".
		" *********************************************************************************************************\n".
		" */\n".
		"\n\n");
	foreach ($configValues as $_configOption => $_configElem) {
		if (is_array($configValues[$_configOption])) {
			$var = "\$configValues['" . $_configOption . "'] = \t\t";
			$var .= var_export($configValues[$_configOption], true);
			$var .= ";\n";
			fwrite($fp, $var);
		} else
	        fwrite($fp, "\$configValues['" . $_configOption . "'] = '" . $configValues[$_configOption] . "';\n");
	}
	fwrite($fp, "\n\n?>");
	fclose($fp);
	$successMsg = "Updated database settings for configuration file";
} else {
	$failureMsg = "Could not open the file for writing: <b>$configFile</b>
	<br/>Check file permissions. The file should be writable by the webserver's user/group";
}

?>
