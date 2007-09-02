<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');
	
	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "id";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";


	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for all accounting records on page: ";
    include('include/config/logging.php');

?>

<?php
	
	include("menu-accounting.php");
	
?>

		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l[Intro][acctall.php]?></a></h2>
				
				<p>
				</p>



<?php

	include 'library/opendb.php';
	include 'include/common/calcs.php';	
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page
	$sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADACCT'].".RadAcctId, ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name as hotspot, ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName, ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause, ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." ON ".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid = ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac;";
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();

	
$sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADACCT'].".RadAcctId, ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name as hotspot, ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName, ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause, ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." ON ".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid = ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac  ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
	$res = $dbSocket->query($sql);

	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	setupLinks($pageNum, $maxPage, $orderBy, $orderType);
	
	if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
		setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);
	/* END */
	echo "<br/>";

	echo "<table border='2' class='table1'>\n";
	echo "
					<thead>
							<tr>
							<th colspan='15'>".$l[all][Records]."</th>
							</tr>
					</thead>
			";

	echo "<thread> <tr>
	<th scope='col'> ".$l[all][ID]."
	<br/>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=radacctid&orderType=asc\"> > </a>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=radacctid&orderType=desc\"> < </a>
	</th>
	<th scope='col'> ".$l[all][HotSpot]."
	<br/>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=hotspot&orderType=asc\"> > </a>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=hotspot&orderType=desc\"> < </a>
	</th>
	<th scope='col'> ".$l[all][Username]."
	<br/>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=username&orderType=asc\"> > </a>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=username&orderType=desc\"> < </a>
	</th>
	<th scope='col'> ".$l[all][IPAddress]."
	<br/>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=framedipaddress&orderType=asc\"> > </a>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=framedipaddress&orderType=desc\"> < </a>
	</th>
	<th scope='col'> ".$l[all][StartTime]."
	<br/>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctstarttime&orderType=asc\"> > </a>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctstarttime&orderType=desc\"> < </a>
	</th>
	<th scope='col'> ".$l[all][StopTime]."
	<br/>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctstoptime&orderType=asc\"> > </a>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctstoptime&orderType=desc\"> < </a>
	</th>
	<th scope='col'> ".$l[all][TotalTime]."
	<br/>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctsessiontime&orderType=asc\"> > </a>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctsessiontime&orderType=desc\"> < </a>
	</th>
	<th scope='col'> ".$l[all][Upload]." (".$l[all][Bytes].")
	<br/>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctinputoctets&orderType=asc\"> > </a>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctinputoctets&orderType=desc\"> < </a>
	</th>
	<th scope='col'> ".$l[all][Download]." (".$l[all][Bytes].")
	<br/>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctoutputoctets&orderType=asc\"> > </a>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctoutputoctets&orderType=desc\"> < </a>
	</th>
	<th scope='col'> ".$l[all][Termination]."
	<br/>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctterminatecause&orderType=asc\"> > </a>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctterminatecause&orderType=desc\"> < </a>
	</th>
	<th scope='col'> ".$l[all][NASIPAddress]."
	<br/>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=nasipaddress&orderType=asc\"> > </a>
	<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=nasipaddress&orderType=desc\"> < </a>
	</th>
	<th scope='col'> ".$l[all][Action]." </th>
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
				<td> <a href='mng-edit.php?username=$row[2]'> ".$l[all][edit]." </a> </td>
		</tr>";
	}
	echo "</table>";

	include 'library/closedb.php';
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
