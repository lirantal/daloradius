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
*
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

    include ("library/checklogin.php");
    //$operator = $_SESSION['operator_user']
	//include('library/check_operator_perm.php');

	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    include('include/config/logging.php');
    
    $cronUser = get_current_user();
    
    isset($_GET['cmd']) ? $cmd = $_GET['cmd'] : $cmd = "";
    
    $dalo_crontab_file = dirname(__FILE__) . '/contrib/scripts/dalo-crontab';
    
    
    switch ($cmd) {
    	
    	case "disable":
    		exec("`which crontab` -u $cronUser -r");
    		break;
    	
    	case "enable":
    		exec("`which crontab` -u $cronUser $dalo_crontab_file");
    		break;
    }
    
    
?>


<?php

    include ("menu-reports-status.php");
    
?>	
		
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"  onclick="javascript:toggleShowDiv('helpPage')">CRON Status
		<h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<br/>
		</div>
		<br/>


<?php
	
	exec("/usr/bin/crontab -u $cronUser -l", $output, $retStatus);
	
?>


	<h3>CRON Entries</h3>

	<a href='rep-stat-cron.php?cmd=enable' >Enable CRON</a>
	&nbsp;
	<a href='rep-stat-cron.php?cmd=disable' >Disable CRON</a>

	<br/>
	<br/>

<?php 
	if ($retStatus != 0):		 
?>
	<font color='red'><b>Error</b> no crontab is configured for this user or user does not exist</font>
	<br/><br/>

<?php
	else:
	
	$i = 0;
		foreach($output as $text) {
			$i++;
			echo "<b>#$i:</b> " . $text . '<br/>';
		}
		
	endif;
?>

		</div>
		
		<div id="footer">

<?php
        include 'page-footer.php';
?>
		
		</div>
		
</div>
</div>


</body>
</html>
