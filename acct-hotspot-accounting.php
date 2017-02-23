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
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['accthotspot.php']; ?>
		<h144>+</h144></a></h2>
				
		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo $l['helpPage']['accthotspotaccounting'] ?>
			<br/>
		</div>
		<br/>



<?php

	include 'library/opendb.php';
	include 'include/management/pages_common.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file
	
	// we can only use the $dbSocket after we have included 'library/opendb.php' which initialzes the connection and the $dbSocket object	
	//$hotspot = $dbSocket->escapeSimple($hotspot);

        // setup php session variables for exporting
        $_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADACCT'];
        //$_SESSION['reportQuery'] = " WHERE ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name='$hotspot'";
        $_SESSION['reportQuery'] = " WHERE ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name='".$dbSocket->escapeSimple($hotspot)."'";
        $_SESSION['reportType'] = "accountingGeneric";

        // escape SQL
        $orderBy = $dbSocket->escapeSimple($orderBy);
        $orderType = $dbSocket->escapeSimple($orderType);

	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page
	$sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADACCT'].".RadAcctId, ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name as hotspot, ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName, ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause, ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." ON ".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid = ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac WHERE ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name='".$dbSocket->escapeSimple($hotspot)."';";
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();



	
	$sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADACCT'].".RadAcctId, ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name as hotspot, ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName, ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause, ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." ON ".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid = ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac WHERE ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name='".$dbSocket->escapeSimple($hotspot)."'  ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
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
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?hotspot=" . urlencode($hotspot) . "&orderBy=radacctid&orderType=" . urlencode($orderTypeNextPage) . " \">"
        .$l['all']['ID']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?hotspot=" . urlencode($hotspot) . "&orderBy=hotspot&orderType=" . urlencode($orderTypeNextPage) . "\">"
        .$l['all']['HotSpot']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?hotspot=" . urlencode($hotspot) . "&orderBy=username&orderType=" . urlencode($orderTypeNextPage) . "\">
		".$l['all']['Username']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?hotspot=" . urlencode($hotspot) . "&orderBy=framedipaddress&orderType=" . urlencode($orderTypeNextPage) . "\">"
        .$l['all']['IPAddress']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?hotspot=" . urlencode($hotspot) . "&orderBy=acctstarttime&orderType=" . urlencode($orderTypeNextPage) . "\">"
        .$l['all']['StartTime']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?hotspot=" . urlencode($hotspot) . "&orderBy=acctstoptime&orderType=" . urlencode($orderTypeNextPage) . "\">"
        .$l['all']['StopTime']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?hotspot=" . urlencode($hotspot) . "&orderBy=acctsessiontime&orderType=" . urlencode($orderTypeNextPage) . "\">"
        .$l['all']['TotalTime']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?hotspot=" . urlencode($hotspot) . "&orderBy=acctinputoctets&orderType=" . urlencode($orderTypeNextPage) . "\">"
        .$l['all']['Upload']." (".$l['all']['Bytes'].")</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?hotspot=" . urlencode($hotspot) . "&orderBy=acctoutputoctets&orderType=" . urlencode($orderTypeNextPage) . "\">"
        .$l['all']['Download']." (".$l['all']['Bytes'].")</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?hotspot=" . urlencode($hotspot) . "&orderBy=acctterminatecause&orderType=" . urlencode($orderTypeNextPage) . "\">"
        .$l['all']['Termination']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?hotspot=" . urlencode($hotspot) . "&orderBy=nasipaddress&orderType=" . urlencode($orderTypeNextPage) . "\">"
        .$l['all']['NASIPAddress']."</a>
		</th>
		</tr> </thread>";
	while($row = $res->fetchRow()) {
                printqn("<tr>
                        <td>" . htmlspecialchars($row[0], ENT_QUOTES) . "</td>
                        <td>" . htmlspecialchars($row[1], ENT_QUOTES) . "</td>

                        <td> <a class='tablenovisit' href='javascript:return;'
                                onClick='javascript:ajaxGeneric(\"include/management/retUserInfo.php\",\"retBandwidthInfo\",\"divContainerUserInfo\",\"username=" . htmlspecialchars($row[2], ENT_QUOTES) . "\");
                                        javascript:__displayTooltip();'
                                tooltipText='
                                        <a class=\"toolTip\" href=\"mng-edit.php?username=" . urlencode($row[2]) . "\">
                                                {$l['Tooltip']['UserEdit']}</a>
                                        <br/><br/>

                                        <div id=\"divContainerUserInfo\">
                                                Loading...
                                        </div>
                                        <br/>'
                                >" . htmlspecialchars($row[2], ENT_QUOTES) . "</a>
                        </td>

                        <td>" . htmlspecialchars($row[3], ENT_QUOTES) . "</td>
                        <td>" . htmlspecialchars($row[4], ENT_QUOTES) . "</td>
                        <td>" . htmlspecialchars($row[5], ENT_QUOTES) . "</td>
                        <td>" . htmlspecialchars(time2str($row[6]), ENT_QUOTES) . " </td>
                        <td>" . htmlspecialchars(toxbyte($row[7]), ENT_QUOTES) . "</td>
                        <td>" . htmlspecialchars(toxbyte($row[8]), ENT_QUOTES) . "</td>
                        <td>" . htmlspecialchars($row[9], ENT_QUOTES) . "</td>
                        <td>" . htmlspecialchars($row[10], ENT_QUOTES) . "</td>
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
