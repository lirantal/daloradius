<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "radacctid";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";	

	if (isset($_REQUEST['limit']))
		$limit = $_REQUEST['limit'];


	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for [$orderBy : $limit] on page: ";


?>

<?php

    include ("menu-reports.php");

?>	
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><?php echo $l['Intro']['reptopusers.php']; ?></a></h2>
				
				<p>
				<?php echo $l['captions']['recordsfortopusers']." ".$order ?> <br/>
				</p>



<?php

	include 'library/opendb.php';
	include 'include/common/calcs.php';

$sql = "SELECT distinct(radacct.UserName), ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime,
sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime) as Time, sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets) as Upload,sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets) as Download, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause, ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress, sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets+".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets) as Bandwidth FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." group by UserName order by $orderBy $orderType limit $limit";

	$res = $dbSocket->query($sql);
	$logDebugSQL .= "";
	$logDebugSQL .= $sql . "\n";

	echo "<table border='2' class='table1'>\n";
	echo "
					<thead>
							<tr>
							<th colspan='10'>".$l['all']['Records']."</th>
							</tr>
					</thead>
			";

	echo "<thread> <tr>
					<th scope='col'> ".$l['all']['Username']."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=username&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=username&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l['all']['IPAddress']."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=framedipaddress&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=framedipaddress&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l['all']['StartTime']."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=acctstarttime&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=acctstarttime&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l['all']['StopTime']."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=acctstoptime&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=acctstoptime&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l['all']['TotalTime']."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=acctsessiontime&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=acctsessiontime&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l['all']['Upload']." (".$l[all][Bytes].")
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=acctinputoctets&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=acctinputoctets&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l['all']['Download']." (".$l[all][Bytes].")
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=acctoutputoctets&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=acctoutputoctets&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l['all']['Termination']."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=acctterminatecause&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=acctterminatecause&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l['all']['NASIPAddress']."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=nasipaddress&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?limit=$limit&orderBy=nasipaddress&orderType=desc\"> < </a>
					</th>
			</tr> </thread>";
	while($row = $res->fetchRow()) {
		echo "<tr>
				<td> $row[0] </td>
				<td> $row[1] </td>
				<td> $row[2] </td>
				<td> $row[3] </td>
				<td> ".seconds2time($row[4])." </td>
				<td> $row[5] - ".bytes2megabytes($row[5])."Mb </td>
				<td> $row[6] - ".bytes2megabytes($row[6])."Mb </td>
				<td> $row[7] </td>
				<td> $row[8] </td>
		</tr>";
	}
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


</body>
</html>
