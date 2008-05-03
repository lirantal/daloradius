<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
	
	include('library/check_operator_perm.php');

	//setting values for the order by and order type variables
	isset($_GET['orderBy']) ? $orderBy = $_GET['orderBy'] : $orderBy = "radacctid";
	isset($_GET['orderType']) ? $orderType = $_GET['orderType'] : $orderType = "asc";	

	isset($_GET['username']) ? $username = $_GET['username'] : $username = "";

	$logDebugSQL = "";

	//feed the sidebar variables
	$accounting_username = $username;


	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for user [$username] on page: ";

?>

<?php
	
	include("menu-accounting.php");
	
?>
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><? echo $l['Intro']['acctusername.php']; ?>
		<h144>+</h144></a></h2>
				
		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo $l['helpPage']['acctusername'] ?>
			<br/>
		</div>
		<br/>



<?php

    include 'library/opendb.php';
	include 'include/management/pages_common.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

	// we can only use the $dbSocket after we have included 'library/opendb.php' which initialzes the connection and the $dbSocket object	
	$username = $dbSocket->escapeSimple($username);	



	// setup php session variables for exporting
	$_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADACCT'];
	$_SESSION['reportQuery'] = " WHERE UserName='$username'";
	$_SESSION['reportType'] = "accountingGeneric";


	
	//checking if the username exist in the db
	$sql = "select * FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." where UserName like '$username'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	if ($res->numRows() != 0) {		//if the user exist display information

	$credit = 0;

	$sql = "SELECT id, UserName, Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='$username' AND ( (Attribute like '%Password') OR (Attribute='Auth-Type') )";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";
	
	echo "
	        <table border='0' class='table1'>
                        <thead>
                                <tr>
                                <th colspan='15'>".$l['all']['Statistics']."</th>
                                </tr>
                        </thead>
	        <thead>
	        <th> ".$l['all']['ID']." </th>
	        <th> ".$l['all']['Username']." </th>
                <th> ".$l['all']['Password']." </th>
                <th> ".$l['all']['Credit']." </th>
                <th> ".$l['all']['Used']." </th>
                <th> ".$l['all']['LeftTime']." </th>
                <th> ".$l['all']['LeftPercent']." </th>
                <th> ".$l['all']['TotalSessions']." </th>
                <th> ".$l['all']['Upload']." (".$l['all']['Bytes'].")</th>
                <th> ".$l['all']['Download']." (".$l['all']['Bytes'].") </th>
        	</th></thead>
        ";
	while($row = $res->fetchRow()) {
	//row[0] = id
	//row[1] = username
	//row[2] = password
        echo "<tr>
        	<td> $row[0] </td>
                <td> $row[1] </td>
                <td> $row[2] </td>
                ";
	}

	$sql = "SELECT Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='$username' ".
		" AND Attribute='Max-All-Session'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";
	$row = $res->fetchRow();
	$credit = $row[0];
	$dateFlag = '';

	if (!($credit)) {
		$sql = "SELECT Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='$username' ".
			" AND Attribute='Max-Daily-Session'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";
		$row = $res->fetchRow();
		$credit = $row[0];
		$dateFlag = " AND (DAY(AcctStartTime) = DAY(NOW())) ";
	}

        if (!($credit)) {
                $sql = "SELECT Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='$username' ".
                        " AND Attribute='Max-Monthly-Session'";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";
                $row = $res->fetchRow();
                $credit = $row[0];
		$dateFlag = " AND (MONTH(AcctStartTime) = MONTH(NOW())) ";
        } 

	if (!($credit)) {
		// the user doesn't have neither of the Max-*-Session limitations, whats left is Expiration
		// or something else (or none at all) but in any case we set the $dateFlag to null;
		$dateFlag = '';
	}

	$sql = "SELECT SUM(AcctSessionTime), COUNT(RadAcctId), SUM(AcctInputOctets), SUM(AcctOutputOctets) FROM ".
		$configValues['CONFIG_DB_TBL_RADACCT'].
		" WHERE UserName='$username' $dateFlag ";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";
	while($row = $res->fetchRow()) {
		//row[0] = used - meaning the total seconds that this user has used so far
		//row[1] = total sessions - the number of time this user has logged on
		//row[2] = total bytes in
		//row[3] = total bytes out
	        $used = $row[0];
	        $total_sessions = $row[1];
	        $total_bytesin = $row[2];
	        $total_bytesout = $row[3];
	}

	if ( (!($credit)) || ($credit == 0) ) { 
        	echo "<td> N/A </td>";
		$remains_per = 'N/A';
	        $remains_time = 'N/A';
	        echo "<td>".time2str($used)."</td>";
		echo "<td>".time2str($remains_time)."</td>";
	} else if ($used > $credit) {
		echo "<td>".time2str($credit)."</td>";
		$remains_per = '0';					// used up more than credit, 0% remains
		$remains_time = ($used-$credit);
	        echo "<td><b><font color='#FF3300'>".time2str($used)."</font></b></td>";
		echo "<td><b><font color='#FF3300'>".time2str($remains_time)." overdue</font></b></td>";
	} else {
		echo "<td>".time2str($credit)."</td>";
	        $remains_per = 100 - round(($used / $credit) * 100);
	        $remains_time = $credit - $used;
	        echo "<td>".time2str($used)."</td>";
		echo "<td>".time2str($remains_time)."</td>";
	}

        echo "<td> $remains_per%</td>";
	echo "<td> $total_sessions </td>";
	echo "<td>".toxbyte($total_bytesin)."</td>";
	echo "<td>".toxbyte($total_bytesout)."</td>";
        echo "</tr>";
	echo "</table> <br/>";

	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page
	
    	$sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADACCT'].".RadAcctId, ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
		".name as hotspot, ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".UserName, ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".FramedIPAddress, ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".AcctStopTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".AcctSessionTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".AcctInputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".AcctOutputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".AcctTerminateCause, ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].
		" LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
		" ON ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".calledstationid = ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
		".mac WHERE UserName='$username';";
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();
	
	$sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADACCT'].".RadAcctId, ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
		".name as hotspot, ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".UserName, ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".FramedIPAddress, ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".AcctStopTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".AcctSessionTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".AcctInputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".AcctOutputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".AcctTerminateCause, ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].
		" LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
		" ON ".$configValues['CONFIG_DB_TBL_RADACCT'].
		".calledstationid = ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
		".mac WHERE UserName='$username' ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
	$res = $dbSocket->query($sql);
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
		setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType,"&username=$username");

        echo " </th></tr>
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
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=radacctid&orderType=$orderType\">
		".$l['all']['ID']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=hotspot&orderType=$orderType\">
		".$l['all']['HotSpot']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=username&orderType=$orderType\">
		".$l['all']['Username']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=framedipaddress&orderType=$orderType\">
		".$l['all']['IPAddress']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=acctstarttime&orderType=$orderType\">
		".$l['all']['StartTime']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=acctstoptime&orderType=$orderType\">
		".$l['all']['StopTime']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=acctsessiontime&orderType=$orderType\">
		".$l['all']['TotalTime']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=acctinputoctets&orderType=$orderType\">
		".$l['all']['Upload']." (".$l['all']['Bytes'].")</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=acctoutputoctets&orderType=$orderType\">
		".$l['all']['Download']." (".$l['all']['Bytes'].")</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=acctterminatecause&orderType=$orderType\">
		".$l['all']['Termination']."</a>
		</th>
		<th scope='col'> 
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=nasipaddress&orderType=$orderType\">
		".$l['all']['NASIPAddress']."</a>
		</th>
                </tr> </thread>";
	while($row = $res->fetchRow()) {

                printqn("<tr>
                        <td> $row[0] </td>

                        <td> <a class='tablenovisit' href='javascript:return;'
                                onClick='javascript:ajaxGeneric(\"include/management/retHotspotInfo.php\",\"retHotspotGeneralStat\",\"divContainerHotspotInfo\",\"hotspot=$row[1]\");
                                        javascript:__displayTooltip();'
                                tooltipText='
                                        <a class=\"toolTip\" href=\"mng-hs-edit.php?name=$row[1]\">
                                                {$l['Tooltip']['HotspotEdit']}</a>
                                        &nbsp;
                                        <a class=\"toolTip\" href=\"acct-hotspot-compare.php?\">
                                                {$l['all']['Compare']}</a>
                                        <br/><br/>

                                        <div id=\"divContainerHotspotInfo\">
                                                Loading...
                                        </div>
                                        <br/>'
                                >$row[1]</a>
                        </td>

                        <td> <a class='tablenovisit' href='javascript:return;'
                                onClick='javascript:ajaxGeneric(\"include/management/retUserInfo.php\",\"retBandwidthInfo\",\"divContainerUserInfo\",\"username=$row[2]\");
                                        javascript:__displayTooltip();'
                                tooltipText='
                                        <a class=\"toolTip\" href=\"mng-edit.php?username=$row[2]\">
	                                        {$l['Tooltip']['UserEdit']}</a>
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
	setupLinks($pageNum, $maxPage, $orderBy, $orderType,"&username=$username");
        echo "
                                                        </th>
                                                        </tr>
                                        </tfoot>
                ";

        echo "</table>";

		} else {
		echo "error: couldn't find this user in the database<br/>";
		}

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
