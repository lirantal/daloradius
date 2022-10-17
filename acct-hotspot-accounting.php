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


	isset($_REQUEST['hotspot']) ? $hotspot = $_REQUEST['hotspot'] : $hotspot = "";


	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for hotspot [$hotspot] on page: ";

?>

<?php
	
	include("menu-accounting-hotspot.php");
	
?>
		
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','accthotspot.php'); ?>
		<h144>&#x2754;</h144></a></h2>
				
		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','accthotspotaccounting') ?>
			<br/>
		</div>
		<br/>



<?php

	include 'library/opendb.php';
	include 'include/management/pages_common.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file
	
	// we can only use the $dbSocket after we have included 'library/opendb.php' which initialzes the connection and the $dbSocket object	
	$hotspot = $dbSocket->escapeSimple($hotspot);

        // setup php session variables for exporting
        $_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADACCT'];
        $_SESSION['reportQuery'] = " WHERE ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name='$hotspot'";
        $_SESSION['reportType'] = "accountingGeneric";


	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page
	$sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADACCT'].".RadAcctId, ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name as hotspot, ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName, ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause, ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." ON ".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid = ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac WHERE ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name='$hotspot';";
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();



	
	$sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADACCT'].".RadAcctId, ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name as hotspot, ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName, ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause, ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." ON ".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid = ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac WHERE ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name='$hotspot'  ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
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
		setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType,"&hotspot=$hotspot");

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
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?hotspot=$hotspot&orderBy=radacctid&orderType=$orderTypeNextPage\">
		".t('all','ID')."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?hotspot=$hotspot&orderBy=hotspot&orderType=$orderTypeNextPage\">
		".t('all','HotSpot')."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?hotspot=$hotspot&orderBy=username&orderType=$orderTypeNextPage\">
		".t('all','Username')."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?hotspot=$hotspot&orderBy=framedipaddress&orderType=$orderTypeNextPage\">
		".t('all','IPAddress')."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?hotspot=$hotspot&orderBy=acctstarttime&orderType=$orderTypeNextPage\">
		".t('all','StartTime')."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?hotspot=$hotspot&orderBy=acctstoptime&orderType=$orderTypeNextPage\">
		".t('all','StopTime')."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?hotspot=$hotspot&orderBy=acctsessiontime&orderType=$orderTypeNextPage\">
		".t('all','TotalTime')."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?hotspot=$hotspot&orderBy=acctinputoctets&orderType=$orderTypeNextPage\">
		".t('all','Upload')." (".t('all','Bytes').")</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?hotspot=$hotspot&orderBy=acctoutputoctets&orderType=$orderTypeNextPage\">
		".t('all','Download')." (".t('all','Bytes').")</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?hotspot=$hotspot&orderBy=acctterminatecause&orderType=$orderTypeNextPage\">
		".t('all','Termination')."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?hotspot=$hotspot&orderBy=nasipaddress&orderType=$orderTypeNextPage\">
		".t('all','NASIPAddress')."</a>
		</th>
		</tr> </thread>";
	while($row = $res->fetchRow()) {
                printqn("<tr>
                        <td> $row[0] </td>
                        <td> $row[1] </td>

                        <td> <a class='tablenovisit' href='#'
                                onClick='javascript:ajaxGeneric(\"include/management/retUserInfo.php\",\"retBandwidthInfo\",\"divContainerUserInfo\",\"username=$row[2]\");return false;'
                                tooltipText='
                                        <a class=\"toolTip\" href=\"mng-edit.php?username=$row[2]\">
                                                ".t('Tooltip','UserEdit')."</a>
                                        <br/><br/>

                                        <div id=\"divContainerUserInfo\">
                                                Loading...
                                        </div>
                                        <br/>'
                                >$row[2]</a>
                        </td>

                        <td> $row[3] </td>
                        <td> $row[4] </td>
                        <td> $row[5] </td>
                        <td> ".time2str($row[6])." </td>
                        <td> ".toxbyte($row[7])."</td>
                        <td> ".toxbyte($row[8])."</td>
                        <td> $row[9] </td>
                        <td> $row[10] </td>
                </tr>");
        }

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='12' align='left'>
        ";
	setupLinks($pageNum, $maxPage, $orderBy, $orderType,"&hotspot=$hotspot");
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
