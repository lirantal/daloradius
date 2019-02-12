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
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "radacctid";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";

	isset($_REQUEST['usernameOnline']) ? $usernameOnline = $_GET['usernameOnline'] : $usernameOnline = "%";	
	
	
	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script src="library/javascript/rounded-corners.js" type="text/javascript"></script>
<script src="library/javascript/form-field-tooltip.js" type="text/javascript"></script>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" href="css/form-field-tooltip.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->
</head>

<?php
        include_once ("library/tabber/tab-layout.php");
?>

<?php

    include ("menu-reports.php");

?>

		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','reponline.php'); ?>
				<h144>&#x2754;</h144></a></h2>
				
		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','reponline'); ?>
			<br/>
		</div>
		<br/>


<div class="tabber">

     <div class="tabbertab" title="Statistics">
        <br/>	
	
<?php

	include 'library/opendb.php';
	include 'include/management/pages_common.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

        // setup php session variables for exporting
        $_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADACCT'];
        $_SESSION['reportQuery'] = " WHERE (AcctStopTime IS NULL OR AcctStopTime = '0000-00-00 00:00:00') AND (UserName LIKE '".$dbSocket->escapeSimple($usernameOnline)."%')";
        $_SESSION['reportType'] = "reportsOnlineUsers";
	
	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page
	$sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADACCT'].".Username, ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress,
			".$configValues['CONFIG_DB_TBL_RADACCT'].".CallingStationId, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime,
			".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress,
			".$configValues['CONFIG_DB_TBL_RADACCT'].".CalledStationId, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionId FROM ".
			$configValues['CONFIG_DB_TBL_RADACCT']." WHERE (".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime IS NULL OR ". 
			$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime = '0000-00-00 00:00:00') AND ".
			" (".$configValues['CONFIG_DB_TBL_RADACCT'].".Username LIKE '".$dbSocket->escapeSimple($usernameOnline)."%')";
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();


	/* we are searching for both kind of attributes for the password, being User-Password, the more
	   common one and the other which is Password, this is also done for considerations of backwards
	   compatibility with version 0.7        */

	   
	$sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADACCT'].".Username, ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress,
			".$configValues['CONFIG_DB_TBL_RADACCT'].".CallingStationId, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime,
			".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress, 
			".$configValues['CONFIG_DB_TBL_RADACCT'].".CalledStationId, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionId, 
			".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets AS Upload,
			".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets AS Download,
			".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name AS hotspot, 
			".$configValues['CONFIG_DB_TBL_RADNAS'].".shortname AS NASshortname, 
			".$configValues['CONFIG_DB_TBL_DALOUSERINFO'].".Firstname AS Firstname, 
			".$configValues['CONFIG_DB_TBL_DALOUSERINFO'].".Lastname AS Lastname".
			" FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].
		" LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." ON (".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac = ".
		$configValues['CONFIG_DB_TBL_RADACCT'].".CalledStationId)".
		" LEFT JOIN ".$configValues['CONFIG_DB_TBL_RADNAS']." ON (".$configValues['CONFIG_DB_TBL_RADNAS'].".nasname = ".
		$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress)".
		" LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOUSERINFO']." ON (".$configValues['CONFIG_DB_TBL_RADACCT'].".Username = ".
		$configValues['CONFIG_DB_TBL_DALOUSERINFO'].".Username)".
		" WHERE (".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime IS NULL OR ".
		$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime = '0000-00-00 00:00:00') AND (".$configValues['CONFIG_DB_TBL_RADACCT'].".Username LIKE '".$dbSocket->escapeSimple($usernameOnline)."%')".
		" ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";

	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	/* END */

	echo "<form name='usersonline' method='get' >";

	echo "<table border='0' class='table1'>\n";
	echo "
		<thead>
			<tr>
			<th colspan='10' align='left'>

                                Select:
                                <a class=\"table\" href=\"javascript:SetChecked(1,'clearSessionsUsers[]','usersonline')\">All</a>

                                <a class=\"table\" href=\"javascript:SetChecked(0,'clearSessionsUsers[]','usersonline')\">None</a>
                        <br/>
                                <input class='button' type='button' value='".t('button','ClearSessions')."' onClick='javascript:removeCheckbox(\"usersonline\",\"mng-del.php\")' />
                                <input class='button' type='button' value='CSV Export'
                                        onClick=\"javascript:window.location.href='include/management/fileExport.php?reportFormat=csv'\"
                                        />
                                <br/><br/>
                ";

	if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
		setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, "&usernameOnline=$usernameOnline");

	echo "</th></tr>
			</thead>
	";

	if ($orderType == "asc") {
			$orderTypeNextPage = "desc";
	} else  if ($orderType == "desc") {
			$orderTypeNextPage = "asc";
	}

	echo "<thread> <tr>
		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?usernameOnline=$usernameOnline&orderBy=username&orderType=$orderTypeNextPage\">
		".t('all','Username'). "</a>
		</th>

		<th scope='col'>
			".t('all','Name')."
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?usernameOnline=$usernameOnline&orderBy=framedipaddress&orderType=$orderTypeNextPage\">
		".t('all','IPAddress')."</a>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?usernameOnline=$usernameOnline&orderBy=acctstarttime&orderType=$orderTypeNextPage\">
		".t('all','StartTime')."</a>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?usernameOnline=$usernameOnline&orderBy=acctsessiontime&orderType=$orderTypeNextPage\">
		".t('all','TotalTime')."</a>
		</th>


		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?usernameOnline=$usernameOnline&orderBy=NASshortname&orderType=$orderTypeNextPage\">
			".t('all','HotSpot')." / 
			".t('all','NasShortname')."
		</th>

		<th scope='col'>
			".t('all','TotalTraffic')."
		</th>		
		

	</tr> </thread>";

	while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {

		$username = $row['Username'];
		$ip = $row['FramedIPAddress'];
		$usermac = $row['CallingStationId'];
		$start = $row['AcctStartTime'];
		$nasip = $row['NASIPAddress'];
		$nasmac = $row['CalledStationId'];
		$hotspot = $row['hotspot'];
		$nasshortname = $row['NASshortname'];
		$acctsessionid = $row['AcctSessionId'];
		$name = $row['Firstname'] . " " . $row['Lastname'];
		
		$upload = toxbyte($row['Upload']);
		$download = toxbyte($row['Download']);
		$traffic = toxbyte($row['Upload']+$row['Download']);

		$totalTime = time2str($row['AcctSessionTime']);

		echo "<tr>
				<td> <input type='checkbox' name='clearSessionsUsers[]' value='$username||$start'>
					<a class='tablenovisit' href='#'
					onclick='javascript:return false;'
					tooltipText=\"
						<a class='toolTip' href='mng-edit.php?username=$username'>".
							t('Tooltip','UserEdit')."</a>
						&nbsp;
						<a class='toolTip' href='config-maint-disconnect-user.php?username=$username&nasaddr=$nasip&customattributes=Acct-Session-Id=$acctsessionid,Framed-IP-Address=$ip'>".
							t('all','Disconnect')."</a>
						<br/>\"
					>$username</a>
					</td>
				<td> $name</td>
				<td> IP: $ip<br/>MAC: $usermac</td>
				<td> $start </td>
				<td> $totalTime </td>
				<td> $hotspot $nasshortname </td>
				<td> ".t('all','Upload').": $upload <br/> ".t('all','Download').": $download <br/> ".t('all','TotalTraffic').": <b>$traffic</b> </td>
		</tr>";
	}

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='10' align='left'>
        ";
        setupLinks($pageNum, $maxPage, $orderBy, $orderType, "&usernameOnline=$usernameOnline");
        echo "
                                                        </th>
                                                        </tr>
                                        </tfoot>
                ";
	
	echo "</table>";
	include 'library/closedb.php';
		
?>

	</div>


     <div class="tabbertab" title="Graph">
        <br/>


<?php
	echo "<center>";
	echo "<img src=\"library/graphs-reports-online-users.php\" />";
	echo "</center>";
?>

	</div>
	
	
	
    <div class="tabbertab" title="Online Nas">
       <br/>


<?php
       echo "<img src=\"library/graphs-reports-online-nas.php\" />";
?>

       </div>

	
	
	
	
	
	
	
	
	
</div>

<?php
	include('include/config/logging.php');
?>
		
		</div>
		
		<div id="footer">
		
								<?php
        include 'page-footer.php';
?>


<script type="text/javascript">
var tooltipObj = new DHTMLgoodies_formTooltip();
tooltipObj.setTooltipPosition('right');
tooltipObj.setPageBgColor('#EEEEEE');
tooltipObj.setTooltipCornerSize(15);
tooltipObj.initFormFieldTooltip();
</script>
		
		</div>
		
</div>
</div>


</body>
</html>
