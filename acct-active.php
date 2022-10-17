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


	isset($_GET['username']) ? $username = $_GET['username'] : $username = "";
	isset($_GET['enddate']) ? $enddate = $_GET['enddate'] : $enddate = "";
	isset($_GET['startdate']) ? $startdate = $_GET['startdate'] : $startdate = "";

	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for active accounting records on page: ";

?>


<?php
	
	include("menu-accounting.php");
	
?>
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','acctactive.php'); ?>
		<h144>&#x2754;</h144></a></h2>
				
                <div id="helpPage" style="display:none;visibility:visible" >
					<?php echo t('helpPage','acctactive') ?>
					<br/>
				</div>
				<br/>


<?php

	include 'library/opendb.php';
	include 'library/datediff.php';
	include 'include/management/pages_common.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file
	$currdate = date("j M Y");

	// we can only use the $dbSocket after we have included 'library/opendb.php' which initialzes the connection and the $dbSocket object
	$username = $dbSocket->escapeSimple($username);
	$enddate = $dbSocket->escapeSimple($enddate);
	$startdate = $dbSocket->escapeSimple($startdate);
	
	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page

	$sql = "select distinct(".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName) as username, ".$configValues['CONFIG_DB_TBL_RADCHECK'].".attribute as attribute, ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Value maxtimeexpiration, sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime) as usedtime from ".$configValues['CONFIG_DB_TBL_RADACCT'].", ".$configValues['CONFIG_DB_TBL_RADCHECK']." where (".$configValues['CONFIG_DB_TBL_RADACCT'].".Username = ".$configValues['CONFIG_DB_TBL_RADCHECK'].".UserName) and (".$configValues['CONFIG_DB_TBL_RADCHECK'].".Attribute = 'Max-All-Session' or ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Attribute = 'Expiration') group by ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName;";
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();
	
	$sql = "select distinct(".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName) as username, ".$configValues['CONFIG_DB_TBL_RADCHECK'].".attribute as attribute, ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Value maxtimeexpiration, sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime) as usedtime from ".$configValues['CONFIG_DB_TBL_RADACCT'].", ".$configValues['CONFIG_DB_TBL_RADCHECK']." where (".$configValues['CONFIG_DB_TBL_RADACCT'].".Username = ".$configValues['CONFIG_DB_TBL_RADCHECK'].".UserName) and (".$configValues['CONFIG_DB_TBL_RADCHECK'].".Attribute = 'Max-All-Session' or ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Attribute = 'Expiration') group by ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName  ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
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
		<br/>
	";

	if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
		setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType,"&username=$username&startdate=$startdate&enddate=$enddate");
	
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
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=username&orderType=$orderTypeNextPage\">
		".t('all','Username')."</a>
		</th>
		<th scope='col'>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=attribute&orderType=$orderTypeNextPage\">
		".t('all','Attribute')."</a>
		</th>
		<th scope='col'>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=maxtimeexpiration&orderType=$orderTypeNextPage\">
		".t('all','MaxTimeExpiration')."</a>
		</th>
		<th scope='col'>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=usedtime&orderType=$orderTypeNextPage\">
		".t('all','UsedTime')."</a>
		</th>
		<th scope='col'> ".t('all','Status')." </th>
		<th scope='col'> ".t('all','Usage')." </th>
		</tr> </thread>";
	
	while($row = $res->fetchRow()) {
		$status="Active";

		if ($row[1] == "Expiration") {		
			if (datediff('d', $row[2], "$currdate", false) > 0) {
				$status = "Expired";
			}
		} 


		if ($row[1] == "Max-All-Session") {		
			if ($row[3] >= $row[2]) {
				$status = "End";
			}
		}

                printqn("<tr>
                        <td> <a class='tablenovisit' href='#'
                                onClick='javascript:ajaxGeneric(\"include/management/retUserInfo.php\",\"retBandwidthInfo\",\"divContainerUserInfo\",\"username=$row[0]\");return false;'
                                tooltipText='
                                        <a class=\"toolTip\" href=\"mng-edit.php?username=$row[0]\">
                                                ".t('Tooltip','UserEdit')."</a>
                                        <br/><br/>

                                        <div id=\"divContainerUserInfo\">
                                                Loading...
                                        </div>
                                        <br/>'
                                >$row[0]</a>
                        </td>

                        <td> $row[1] </td>
                        <td> $row[2] </td>
                        <td>".time2str($row[3])."</td>
                        <td> $status </td>
			<td> ");

		if ($row[1] == "Expiration") {		
			$difference = datediff('d', $row[2], "$currdate", false);
			if ($difference > 0)
				echo "<h100> " . " $difference days since expired" . "</h100> ";
			else 
				echo substr($difference, 1) . " days until expiration";
		} 

		if ($row[1] == "Max-All-Session") {		
			if ($status == "End") {
				echo "<h100> " . abs($row[2] - $row[3]) . " seconds overdue credit" . "</h100>";
			} else {
				echo $row[2] - $row[3];
				echo " left on credit";
			}
		} 


		echo "	</td>
                </tr>";
        }

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='12' align='left'>
        ";
	setupLinks($pageNum, $maxPage, $orderBy, $orderType,"&username=$username&startdate=$startdate&enddate=$enddate");
        echo "
                                                        </th>
                                                        </tr>
                                        </tfoot>
                ";

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

<script type="text/javascript">
        var tooltipObj = new DHTMLgoodies_formTooltip();
        tooltipObj.setTooltipPosition('right');
        tooltipObj.setPageBgColor('#EEEEEE');
        tooltipObj.setTooltipCornerSize(15);
        tooltipObj.initFormFieldTooltip();
</script>

</body>
</html>
