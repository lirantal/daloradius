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
	isset($_GET['orderType']) ? $orderType = $_GET['orderType'] : $orderType = "asc";

        isset($_GET['ratename']) ? $ratename = $_GET['ratename'] : $ratename = "";
        isset($_GET['username']) ? $username = $_GET['username'] : $username = "%";
        isset($_GET['startdate']) ? $startdate = $_GET['startdate'] : $startdate = "";
        isset($_GET['enddate']) ? $enddate = $_GET['enddate'] : $enddate = "";

	//feed the sidebar variables
        $billing_date_ratename = $ratename;
        $billing_date_username = $username;
        $billing_date_startdate = $startdate;
        $billing_date_enddate = $enddate;

	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for user [$username] and start date [$startdate] and end date [$enddate] on page: ";
	$logDebugSQL = "";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
</head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<?php

	include("menu-bill-rates.php");

?>

	<div id="contentnorightbar">

		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','billratesdate.php'); ?>
		<h144>&#x2754;</h144></a></h2>
		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','billratesdate') ?>
			<br/>
		</div>
		<br/>


<?php

	include 'library/opendb.php';
	include 'include/management/pages_common.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

	// we can only use the $dbSocket after we have included 'library/opendb.php' which initialzes the connection and the $dbSocket object
	$username = $dbSocket->escapeSimple($username);
	$startdate = $dbSocket->escapeSimple($startdate);
	$enddate = $dbSocket->escapeSimple($enddate);
	$ratename = $dbSocket->escapeSimple($ratename);

        include_once('include/management/userBilling.php');
        userBillingRatesSummary($username, $startdate, $enddate, $ratename, 1);				// draw the billing rates summary table

        include 'library/opendb.php';

	// get rate type
	$sql = "SELECT rateType FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES']." WHERE ".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES'].".rateName = '$ratename'";
	$res = $dbSocket->query($sql);

	if ($res->numRows() == 0)
		$failureMsg = "Rate was not found in database, check again please";
	else {

		$row = $res->fetchRow();
		list($ratetypenum, $ratetypetime) = explode("/",$row[0]);

		switch ($ratetypetime) {					// we need to translate any kind of time into seconds, so a minute is 60 seconds, an hour is 3600,
			case "second":						// and so on...
				$multiplicate = 1;
				break;
			case "minute":
				$multiplicate = 60;
				break;
			case "hour":
				$multiplicate = 3600;
				break;
			case "day":
				$multiplicate = 86400;
				break;
			case "week":
				$multiplicate = 604800;
				break;
			case "month":
				$multiplicate = 187488000;			// a month is 31 days
				break;
			default:
				$multiplicate = 0;
				break;
		}

		// then the rate cost would be the amount of seconds times the prefix multiplicator thus:
		$rateDivisor = ($ratetypenum * $multiplicate);
	}

	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page
	$sql = "SELECT distinct(".$configValues['CONFIG_DB_TBL_RADACCT'].".username), ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress, ".
		$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".
		$configValues['CONFIG_DB_TBL_DALOBILLINGRATES'].".rateCost ".
		" FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].", ".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES']." WHERE (AcctStartTime >= '$startdate') and (AcctStartTime <= '$enddate') and (UserName = '$username') and (".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES'].".rateName = '$ratename')";
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();


	$sql = "SELECT distinct(".$configValues['CONFIG_DB_TBL_RADACCT'].".username), ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress, ".
		$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".
		$configValues['CONFIG_DB_TBL_DALOBILLINGRATES'].".rateCost ".
		" FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].", ".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES']." WHERE (AcctStartTime >= '$startdate') and (AcctStartTime <= '$enddate') and (UserName = '$username') and (".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES'].".rateName = '$ratename')".
		" ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";

	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	/* END */



	if (isset($failureMsg)) {
		include_once('include/management/actionMessages.php');
		echo "<br/>";
	}


	echo "<table border='0' class='table1'>\n";
        echo "
                <thead>
                        <tr>
                        <th colspan='12' align='left'>

                        <br/>
                <br/>
        ";

	if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
		setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType,"&username=$username&ratename=$ratename&startdate=$startdate&enddate=$enddate");

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
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&ratename=$ratename&startdate=$startdate&enddate=$enddate&orderBy=username&orderType=$orderTypeNextPage\">
		".t('all','Username')."</a>
		</th>
		<th scope='col'>
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&ratename=$ratename&startdate=$startdate&enddate=$enddate&orderBy=nasipaddress&orderType=$orderTypeNextPage\">
		".t('all','NASIPAddress')."</a>
		</th>
		<th scope='col'>
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&ratename=$ratename&startdate=$startdate&enddate=$enddate&orderBy=acctstarttime&orderType=$orderTypeNextPage\">
		".t('all','LastLoginTime')."</a>
		</th>
		<th scope='col'>
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&ratename=$ratename&startdate=$startdate&enddate=$enddate&orderBy=acctsessiontime&orderType=$orderTypeNextPage\">
		".t('all','TotalTime')."</a>
		</th>
		<th scope='col'>
		<br/>
		 ".t('all','Billed')."
		</th>
                </tr> </thread>";

	$sumBilled = 0;
	$sumSession = 0;

	while($row = $res->fetchRow()) {

		$sessionTime = $row[3];
		$rateCost = $row[4];
		$billed = (($sessionTime/$rateDivisor)*$rateCost);
		$sumBilled += $billed;
		$sumSession += $sessionTime;

		echo "<tr>
				<td> $row[0] </td>
				<td> $row[1] </td>
				<td> $row[2] </td>
				<td> ".time2str($row[3])." </td>
				<td> ".number_format($billed,2)." </td>
		</tr>";

	}

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='12' align='left'>
        ";
	setupLinks($pageNum, $maxPage, $orderBy, $orderType,"&username=$username&ratename=$ratename&startdate=$startdate&enddate=$enddate");
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

</body>
</html>
