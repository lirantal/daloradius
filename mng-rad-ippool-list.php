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
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "id";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";



	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" href="css/form-field-tooltip.css" type="text/css" media="screen,projection" />
</head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script src="library/javascript/rounded-corners.js" type="text/javascript"></script>
<script src="library/javascript/form-field-tooltip.js" type="text/javascript"></script>
<?php
	include ("menu-mng-rad-ippool.php");
?>

	<div id="contentnorightbar">
	
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngradippoollist.php') ?>
		<h144>&#x2754;</h144></a></h2>
		
		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','mngradippoollist') ?>
			<br/>
		</div>
		<br/>


<?php

	include 'library/opendb.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

	//orig: used as method to get total rows - this is required for the pages_numbering.php page	
	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADIPPOOL'];
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";

	$numrows = $res->numRows();

	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADIPPOOL'].
			" ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	/* END */


	echo "<form name='listallippool' method='post' action='mng-rad-ippool-del.php'>";

	echo "<table border='0' class='table1'>\n";
	echo "
		<thead>
			<tr>
			<th colspan='10' align='left'>

			Select:
			<a class=\"table\" href=\"javascript:SetChecked(1,'poolname[]','listallippool')\">All</a>
			<a class=\"table\" href=\"javascript:SetChecked(0,'poolname[]','listallippool')\">None</a>
			<br/>
			<input class='button' type='button' value='Delete' onClick='javascript:removeCheckbox(\"listallippool\",\"mng-rad-ippool-del.php\")' />
			<br/><br/>
	";

	if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
		setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);

	echo "	</th></tr>
			</thead>
	";

	if ($orderType == "asc") {
		$orderType = "desc";
	} else  if ($orderType == "desc") {
		$orderType = "asc";
	}

	echo "<thread> <tr>
		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=id&orderType=$orderType\">
		".t('all','ID')."</a>
		<br/>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=pool_name&orderType=$orderType\">
		".t('all','PoolName')."</a>
		<br/>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=framedipaddress&orderType=$orderType\">
		".t('all','IPAddress')."</a>
		<br/>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=nasipaddress&orderType=$orderType\">
		".t('all','NASIPAddress')."</a>
		<br/>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=CalledStationId&orderType=$orderType\">
		".t('all','CalledStationId')."</a>
		<br/>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=CallingStationID&orderType=$orderType\">
		".t('all','CallingStationID')."</a>
		<br/>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=expiry_time&orderType=$orderType\">
		".t('all','ExpiryTime')."</a>
		<br/>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=username&orderType=$orderType\">
		".t('all','Username')."</a>
		<br/>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=pool_key&orderType=$orderType\">
		".t('all','PoolKey')."</a>
		<br/>
		</th>
	</tr> </thread>";
	while($row = $res->fetchRow()) {
		echo "<tr>
                                <td> <input type='checkbox' name='poolname[]' value='$row[1]||$row[2]'> $row[0] </td>
				<td> $row[1] </td>
                                <td> <a class='tablenovisit' href='#'
								onclick='javascript:return false;'
                                tooltipText=\"
                                        <a class='toolTip' href='mng-rad-ippool-edit.php?poolname=$row[1]&ipaddressold=$row[2]'>".t('Tooltip','EditIPAddress')."</a>
					<br/>
                                        <a class='toolTip' href='mng-rad-ippool-del.php?poolname=$row[1]&ipaddress=$row[2]'>".t('Tooltip','RemoveIPAddress')."</a>
                                        <br/>\"
                                        >$row[2]</a></td>
				<td> $row[3] </td>
				<td> $row[4] </td>
				<td> $row[5] </td>
				<td> $row[6] </td>
				<td> $row[7] </td>
				<td> $row[8] </td>
		</tr>";
	}

	echo "
		<tfoot>
				<tr>
				<th colspan='10' align='left'>
	";
	setupLinks($pageNum, $maxPage, $orderBy, $orderType);
	echo "
			</th>
			</tr>
		</tfoot>
	";


	echo "</table></form>";

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

<script type="text/javascript">
var tooltipObj = new DHTMLgoodies_formTooltip();
tooltipObj.setTooltipPosition('right');
tooltipObj.setPageBgColor('#EEEEEE');
tooltipObj.setTooltipCornerSize(15);
tooltipObj.initFormFieldTooltip();
</script>

</body>
</html>
