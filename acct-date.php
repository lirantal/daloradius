<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "radacctid";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";	

	
	$username = $dbSocket->escapeSimple($_REQUEST['username']);
	$startdate = $dbSocket->escapeSimple($_REQUEST['startdate']);
	$enddate = $dbSocket->escapeSimple($_REQUEST['enddate']);



	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for user [$username] and start date [$startdate] and end date [$enddate] on page: ";

?>

<?php
	
	include("menu-accounting.php");
	
?>

		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><? echo $l['Intro']['acctdate.php']; ?></a></h2>
				
                <div id="helpPage" style="display:none;visibility:visible" >
			<?php echo $l['helpPage']['acctdate'] ?>		
		</div>
		<br/>



<?php

	include 'library/opendb.php';
	include 'include/common/calcs.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page
    $sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADACCT'].".RadAcctId, ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name as hotspot, ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName, ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause, ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." ON ".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid = ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac WHERE AcctStartTime>'$startdate' and AcctStartTime<'$enddate' and UserName like '$username';";
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();

	
    $sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADACCT'].".RadAcctId, ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name as hotspot, ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName, ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause, ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." ON ".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid = ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac WHERE AcctStartTime>'$startdate' and AcctStartTime<'$enddate' and UserName like '$username' ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";

	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	/* END */

	$counter=0;
	$bytesin=0;
	$bytesout=0;
	$megabytesout=0;
	$megabytesin=0;
	$session_seconds=0;
	$session_minutes=0;

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='15'>".$l['all']['Records']."</th>
                                </tr>

                                                        <tr>
                                                        <th colspan='12' align='left'>
                <br/>
        ";

        if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
		setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType,"&username=$username&startdate=$startdate&enddate=$enddate");

        echo " </th></tr>
                                        </thead>

                        ";

        echo "<thread> <tr>
		<th scope='col'> ".$l['all']['ID']."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=radacctid&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=radacctid&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l['all']['HotSpot']."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=hotspot&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=hotspot&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l['all']['Username']."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=username&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=username&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l['all']['IPAddress']."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=framedipaddress&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=framedipaddress&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l['all']['StartTime']."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=acctstarttime&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=acctstarttime&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l['all']['StopTime']."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=acctstoptime&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=acctstoptime&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l['all']['TotalTime']."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=acctsessiontime&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=acctsessiontime&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l['all']['Upload']." (".$l['all']['Bytes'].")
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=acctinputoctets&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=acctinputoctets&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l['all']['Download']." (".$l['all']['Bytes'].")
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=acctoutputoctets&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=acctoutputoctets&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l['all']['Termination']."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=acctterminatecause&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=acctterminatecause&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l['all']['NASIPAddress']."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=nasipaddress&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&startdate=$startdate&enddate=$enddate&orderBy=nasipaddress&orderType=desc\"> < </a>
		</th>
        <th scope='col'> ".$l['all']['Action']." </th>
                </tr> </thread>";

	while($row = $res->fetchRow()) {
		echo "<tr>
				<td> $row[0] </td>
				<td> $row[1] </td>
				<td> $row[2] </td>
				<td> $row[3] </td>
				<td> $row[4] </td>
				<td> $row[5] </td>
				<td> ".seconds2time($row[6])." </td>
				<td> $row[7] - ".bytes2megabytes($row[7])."Mb </td>
				<td> $row[8] - ".bytes2megabytes($row[8])."Mb </td>
				<td> $row[9] </td>
				<td> $row[10] </td>
				<td> <a href='mng-edit.php?username=$row[2]'> ".$l['all']['edit']." </a> </td>
		</tr>";

	$counter++;
	$session_seconds += $row[5];
//	        $session_minutes = int($session_seconds / 60);
	$bytesin= $bytesin + $row[6];
	$bytesout= $bytesout + $row[7];
//	        $megabytesin = int($bytesin / 1000000);
//	        $megabytesout = int($bytesout / 1000000);

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
