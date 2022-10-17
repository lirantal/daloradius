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
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


	//setting values for the order by and order type variables
	isset($_GET['orderBy']) ? $orderBy = $_GET['orderBy'] : $orderBy = "radacctid";
	isset($_GET['orderType']) ? $orderType = $_GET['orderType'] : $orderType = "desc";

	if (isset($_GET['limit']))
		$limit = $_GET['limit'];
	if (isset($_GET['startdate']))
		$startdate = $_GET['startdate'];
	if (isset($_GET['enddate']))
		$enddate = $_GET['enddate'];
	if (isset($_GET['username']))
		$username = $_GET['username'];







	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for [$orderBy : $limit] on page: ";


?>

<?php

    include ("menu-reports.php");

?>	
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','reptopusers.php'); ?>
		<h144>&#x2754;</h144></a></h2></a></h2>
				

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','reptopusers')." ".$orderBy ?>
			<br/>
		</div>
		<br/>

<?php

	include 'library/opendb.php';
	include 'include/management/pages_common.php';	
	

	$sql = "SELECT distinct(radacct.UserName), ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress, ".
		$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime,max( ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".AcctStopTime), sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime) as Time, ".
		" sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets) as Upload,sum(".
		$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets) as Download, ".
		$configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause, ".
		$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress, sum(".
		$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets+".
		$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets) as Bandwidth FROM ".
		$configValues['CONFIG_DB_TBL_RADACCT']." WHERE AcctStopTime > '0000-00-00 00:00:01' AND AcctStartTime>'$startdate' AND AcctStartTime< '$enddate' AND (Username LIKE '$username') group by UserName order by $orderBy $orderType limit $limit";

        // setup php session variables for exporting
        $_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADACCT'];
        $_SESSION['reportQuery'] = " WHERE AcctStopTime > '0000-00-00 00:00:01' AND AcctStartTime>'$startdate' AND AcctStartTime< '$enddate' AND (Username LIKE '$username')";
        $_SESSION['reportType'] = "TopUsers";

	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";

	echo "<table border='0' class='table1'>\n";
	echo "
						<input class='button' type='button' value='CSV Export'
                                        	onClick=\"javascript:window.location.href='include/management/fileExport.php?reportFormat=csv'\"
                                        	/>

					<thead>
							<tr>
							<th colspan='10'>".t('all','Records')."</th>
							</tr>
					</thead>
			";

			
	if ($orderType == "asc") {
			$orderType = "desc";
	} else  if ($orderType == "desc") {
			$orderType = "asc";
	}
		
	echo "<thread> <tr>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=username&orderType=$orderType&username=$username&startdate=$startdate&enddate=$enddate\">
		".t('all','Username')." </a>
		</th>
		<th scope='col'>
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=framedipaddress&orderType=$orderType&username=$username&startdate=$startdate&enddate=$enddate\">
		".t('all','IPAddress')."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=acctstarttime&orderType=$orderType&username=$username&startdate=$startdate&enddate=$enddate\">
		".t('all','StartTime')."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=acctstoptime&orderType=$orderType&username=$username&startdate=$startdate&enddate=$enddate\">
		".t('all','StopTime')."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=Time&orderType=$orderType&username=$username&startdate=$startdate&enddate=$enddate\">
		".t('all','TotalTime')."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=Upload&orderType=$orderType&username=$username&startdate=$startdate&enddate=$enddate\">
		".t('all','Upload')." (".t('all','Bytes').")</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=Download&orderType=$orderType&username=$username&startdate=$startdate&enddate=$enddate\">
		".t('all','Download')." (".t('all','Bytes').")</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=acctterminatecause&orderType=$orderType&username=$username&startdate=$startdate&enddate=$enddate\">
		".t('all','Termination')."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=nasipaddress&orderType=$orderType&username=$username&startdate=$startdate&enddate=$enddate\">
		".t('all','NASIPAddress')."</a>
		</th>
		</tr> </thread>";
		
	while($row = $res->fetchRow()) {
		echo "<tr>
				<td> $row[0] </td>
				<td> $row[1] </td>
				<td> $row[2] </td>
				<td> $row[3] </td>
				<td> ".time2str($row[4])."</td>
				<td> ".toxbyte($row[5])."</td>
				<td> ".toxbyte($row[6])."</td>
				<td> $row[7] </td>
				<td> $row[8] </td>
		</tr>";
	}
	echo "</table>";

	include 'library/closedb.php';
?>



<?php
	include('include/config/logging.php');
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
