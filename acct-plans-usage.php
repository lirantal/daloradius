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
	isset($_GET['orderBy']) ? $orderBy = $_GET['orderBy'] : $orderBy = "username";
	isset($_GET['orderType']) ? $orderType = $_GET['orderType'] : $orderType = "asc";

	if ( (isset($_GET['username'])) && ($_GET['username']) ) {
		$username = $_GET['username'];
	} else {
		$username = "%";
	}
	
	if ( (isset($_GET['planname'])) && ($_GET['planname']) ) {
		$planname = $_GET['planname'];
	} else {
		$planname = "%";
	}

	isset($_GET['startdate']) ? $startdate = $_GET['startdate'] : $startdate = "";
	isset($_GET['enddate']) ? $enddate = $_GET['enddate'] : $enddate = "";
	
	//feed the sidebar variables
	$accounting_plan_username = $username;
	$accounting_plan_startdate = $startdate;
	$accounting_plan_enddate = $enddate;

	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for user [$username] and start date [$startdate] and end date [$enddate] on page: ";
	$logDebugSQL = "";

?>

<?php
	
	include("menu-accounting-plans.php");
	
?>

	<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','acctplans.php'); ?>
		<h144>&#x2754;</h144></a></h2>
		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','acctplans') ?>
			<br/>
		</div>
		<br/>


<?php

	include 'library/opendb.php';
	include 'include/management/pages_common.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

	// we can only use the $dbSocket after we have included 'library/opendb.php' which initialzes the connection and the $dbSocket object	
	$username = $dbSocket->escapeSimple($username);
	$planname = $dbSocket->escapeSimple($planname);
	$startdate = $dbSocket->escapeSimple($startdate);
	$enddate = $dbSocket->escapeSimple($enddate);

	// setup php session variables for exporting
	$_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADACCT'];
	$_SESSION['reportQuery'] = 	" WHERE ".
			"(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username LIKE '$username')".
			" AND ".
			"(".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planname LIKE '$planname')".
			" AND ".
			"(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username = ".$configValues['CONFIG_DB_TBL_RADACCT'].".username)".
			" AND ".
			"(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planname = ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planname)".
			" AND ".
			"(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime > '$startdate' )".
			" AND ".
			"(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime < '$enddate' )".
			" GROUP BY ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username";
	$_SESSION['reportType'] = "reportsPlansUsage";

	include 'library/closedb.php';

	include_once('include/management/userReports.php');
	userPlanInformation($username, 1);
	userSubscriptionAnalysis($username, 1);                 // userSubscriptionAnalysis with argument set to 1 for drawing the table
	userConnectionStatus($username, 1);                     // userConnectionStatus (same as above)

	include 'library/opendb.php';
	
	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page
	$sql = "".
		"SELECT ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username as username,".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planname as planname,".
			"SUM(".$configValues['CONFIG_DB_TBL_RADACCT'].".acctsessiontime) as sessiontime,".
			"SUM(".$configValues['CONFIG_DB_TBL_RADACCT'].".acctinputoctets) as upload,".
			"SUM(".$configValues['CONFIG_DB_TBL_RADACCT'].".acctoutputoctets) as download,".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planTimeBank as planTimeBank,".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planTimeType as planTimeType".
		" FROM ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].",".
			$configValues['CONFIG_DB_TBL_RADACCT'].",".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
		" WHERE ".
			"(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username LIKE '$username')".
			" AND ".
			"(".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planname LIKE '$planname')".
			" AND ".
			"(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username = ".$configValues['CONFIG_DB_TBL_RADACCT'].".username)".
			" AND ".
			"(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planname = ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planname)".
			" AND ".
			"(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime > '$startdate' )".
			" AND ".
			"(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime < '$enddate' )".
			" GROUP BY ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username";
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();

	
 	$sql = "".
		"SELECT ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username as username,".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planname as planname,".
			"SUM(".$configValues['CONFIG_DB_TBL_RADACCT'].".acctsessiontime) as sessiontime,".
			"SUM(".$configValues['CONFIG_DB_TBL_RADACCT'].".acctinputoctets) as upload,".
			"SUM(".$configValues['CONFIG_DB_TBL_RADACCT'].".acctoutputoctets) as download,".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planTimeBank as planTimeBank,".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planTimeType as planTimeType".
		" FROM ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].",".
			$configValues['CONFIG_DB_TBL_RADACCT'].",".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
		" WHERE ".
			"(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username LIKE '$username')".
			" AND ".
			"(".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planname LIKE '$planname')".
			" AND ".
			"(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username = ".$configValues['CONFIG_DB_TBL_RADACCT'].".username)".
			" AND ".
			"(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planname = ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planname)".
			" AND ".
			"(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime > '$startdate' )".
			" AND ".
			"(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime < '$enddate' )".
			" GROUP BY ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";

	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	/* END */


	echo "<table border='0' class='table1'>\n";
        echo "
                <thead>
                        <tr>
                        <th colspan='12' align='left'>

                        <input class='button' type='button' value='CSV Export'
                        onClick=\"javascript:window.location.href='include/management/fileExport.php?reportFormat=csv'\"
                        />
                        <br/>
                <br/>
        ";

	if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
		setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType,"&username=$username&startdate=$startdate&enddate=$enddate&planname=$planname");

	echo " </th></tr>
			</thead>
	";

	if ($orderType == "asc") {
			$orderTypeNextPage = "desc";
	} else  if ($orderType == "desc") {
			$orderTypeNextPage = "asc";
	}
	
        echo "<thread> <tr>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&planname=$planname&orderBy=username&orderType=$orderTypeNextPage\">
		".t('all','Username')."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&planname=$planname&orderBy=planname&orderType=$orderTypeNextPage\">
		".t('all','PlanName')."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&planname=$planname&orderBy=sessiontime&orderType=$orderTypeNextPage\">
		".t('all','UsedTime')."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&planname=$planname&orderBy=plantimebank&orderType=$orderTypeNextPage\">
		".t('all','TotalTime')."</a>
		</th>
		<th scope='col'> 
		<br/>
		".t('all','TotalTraffic')." (".t('all','Bytes').")</a>
		</th>
                </tr> </thread>";

	while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
		
		$perc = number_format((($row['sessiontime']/$row['planTimeBank'])*100),2);
		if (($perc-100) > 0)
			$percFormatted = "<font color='red'>$perc</font>";
		else
			$percFormatted = "$perc";
			
		printqn("<tr>
                        <td> <a class='tablenovisit' href='#'
						onClick='javascript:ajaxGeneric(\"include/management/retUserInfo.php\",\"retBandwidthInfo\",\"divContainerUserInfo\",\"username={$row['username']}\");return false;'
                                tooltipText='
								<a class=\"toolTip\" href=\"bill-pos-edit.php?username={$row['username']}\">
	                                        ".t('Tooltip','UserEdit')."</a>
                                        <br/><br/>

                                        <div id=\"divContainerUserInfo\">
                                                Loading...
                                        </div>
                                        <br/>'
									>{$row['username']}</a>
                        </td>
				<td> {$row['planname']} </td>
				<td> 
					".time2str($row['sessiontime'])."
					<b>($percFormatted)%</b>
					</td>
				<td> ".time2str($row['planTimeBank'])." </td>
				<td> ".toxbyte($row['upload'] + $row['download'])."</td>
		</tr>");

	}

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='12' align='left'>
        ";
	setupLinks($pageNum, $maxPage, $orderBy, $orderType,"&username=$username&startdate=$startdate&enddate=$enddate&planname=$planname");
        echo "
                                                        </th>
                                                        </tr>
                                        </tfoot>
                ";

	echo "</table>";

	include 'library/closedb.php';
?>
			
		</div>


<?php
	include('include/config/logging.php');
?>

		<div id="footer">
		
								<?php
        include 'page-footer.php';
?>

		
		</div>
		
</div>
</div>

<script type="text/javascript">
        var tooltipObj = new DHTMLgoodies_formTooltip();
        tooltipObj.setTooltipPosition('right');
        tooltipObj.setPageBgColor('#EEEEEE');
        tooltipObj.setTooltipCornerSize(15);
        tooltipObj.initFormFieldTooltip();
</script>

</body>
</html>
