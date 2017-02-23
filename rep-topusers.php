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
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['reptopusers.php']; ?>
		<h144>+</h144></a></h2></a></h2>
				

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo $l['helpPage']['reptopusers'] . " " . htmlspecialchars($orderBy, ENT_QUOTES) ?>
			<br/>
		</div>
		<br/>

<?php

	include 'library/opendb.php';
	include 'include/management/pages_common.php';	

    $orderBy = $dbSocket->escapeSimple($orderBy);
    $orderType = $dbSocket->escapeSimple($orderType);	

	$sql = "SELECT distinct(radacct.UserName), ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress, ".
		$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime,max( ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".AcctStopTime), sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime) as Time, ".
		" sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets) as Upload,sum(".
		$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets) as Download, ".
		$configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause, ".
		$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress, sum(".
		$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets+".
		$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets) as Bandwidth FROM ".
		$configValues['CONFIG_DB_TBL_RADACCT']." WHERE AcctStopTime > '0000-00-00 00:00:01' AND AcctStartTime>'".$dbSocket->escapeSimple($startdate)."' AND AcctStartTime< '".$dbSocket->escapeSimple($enddate)."' AND (Username LIKE '".$dbSocket->escapeSimple($username)."') group by UserName order by $orderBy $orderType limit $limit";

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
							<th colspan='10'>".$l['all']['Records']."</th>
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
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?limit=" . urlencode($limit) . "&orderBy=username&orderType=" . urlencode($orderType) . "&username=" . urlencode($username) . "&startdate=" . urlencode($startdate) . "&enddate=" . urlencode($enddate) . "\">
		".$l['all']['Username']." </a>
		</th>
		<th scope='col'>
		<br/>
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?limit=" . urlencode($limit) . "&orderBy=framedipaddress&orderType=" . urlencode($orderType) . "&username=" . urlencode($username) . "&startdate=" . urlencode($startdate) . "&enddate=" . urlencode($enddate) . "\">
		".$l['all']['IPAddress']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?limit=" . urlencode($limit) . "&orderBy=acctstarttime&orderType=" . urlencode($orderType) . "&username=" . urlencode($username) . "&startdate=" . urlencode($startdate) . "&enddate=" . urlencode($enddate) . "\">
		".$l['all']['StartTime']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?limit=" . urlencode($limit) . "&orderBy=acctstoptime&orderType=" . urlencode($orderType) . "&username=" . urlencode($username) . "&startdate=" . urlencode($startdate) . "&enddate=" . urlencode($enddate) . "\">
		".$l['all']['StopTime']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?limit=" . urlencode($limit) . "&orderBy=Time&orderType=" . urlencode($orderType) . "&username=" . urlencode($username) . "&startdate=" . urlencode($startdate) . "&enddate=" . urlencode($enddate) . "\">
		".$l['all']['TotalTime']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?limit=" . urlencode($limit) . "&orderBy=Upload&orderType=" . urlencode($orderType) . "&username=" . urlencode($username) . "&startdate=" . urlencode($startdate) . "&enddate=" . urlencode($enddate) . "\">
		".$l['all']['Upload']." (".$l['all']['Bytes'].")</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?limit=" . urlencode($limit) . "&orderBy=Download&orderType=" . urlencode($orderType) . "&username=" . urlencode($username) . "&startdate=" . urlencode($startdate) . "&enddate=" . urlencode($enddate) . "\">
		".$l['all']['Download']." (".$l['all']['Bytes'].")</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?limit=" . urlencode($limit) . "&orderBy=acctterminatecause&orderType=" . urlencode($orderType) . "&username=" . urlencode($username) . "&startdate=" . urlencode($startdate) . "&enddate=" . urlencode($enddate) . "\">
		".$l['all']['Termination']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?limit=" . urlencode($limit) . "&orderBy=nasipaddress&orderType=" . urlencode($orderType) . "&username=" . urlencode($username) . "&startdate=" . urlencode($startdate) . "&enddate=" . urlencode($enddate) . "\">
		".$l['all']['NASIPAddress']."</a>
		</th>
		</tr> </thread>";
		
	while($row = $res->fetchRow()) {
		echo "<tr>
				<td> " . htmlspecialchars($row[0], ENT_QUOTES) . " </td>
				<td> " . htmlspecialchars($row[1], ENT_QUOTES) . " </td>
				<td> " . htmlspecialchars($row[2], ENT_QUOTES) . " </td>
				<td> " . htmlspecialchars($row[3], ENT_QUOTES) . " </td>
				<td> " . htmlspecialchars(time2str($row[4]), ENT_QUOTES) . "</td>
				<td> " . htmlspecialchars(toxbyte($row[5]), ENT_QUOTES) . "</td>
				<td> " . htmlspecialchars(toxbyte($row[6]), ENT_QUOTES) . "</td>
				<td> " . htmlspecialchars($row[7], ENT_QUOTES) . " </td>
				<td> " . htmlspecialchars($row[8], ENT_QUOTES) . " </td>
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
