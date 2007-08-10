<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
	
	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "radacctid";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";	

	$username = $_REQUEST['username'];


	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for user [$username] on page: ";
    include('include/config/logging.php');

?>

<?php
	
	include("menu-accounting.php");
	
?>
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l[Intro][acctusername.php]; ?></a></h2>
				
				<p>
				</p>



<?php

    include 'library/opendb.php';
	include 'include/common/calcs.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file


	//checking if the username exist in the db
	$sql = "select * FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." where UserName like '$username'";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
	if (mysql_num_rows($res) != 0) {		//if the user exist display information

	$credit = 0;

	$sql = "SELECT id, UserName, Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName LIKE '$username' AND (Attribute like '%Password')";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

	echo "
	        <table border='2' class='table1'>
                        <thead>
                                <tr>
                                <th colspan='15'>".$l[all][Statistics]."</th>
                                </tr>
                        </thead>
	        <thead><tr >
	        <td> ".$l[all][ID]." </td>
	        <td> ".$l[all][Username]." </td>
                <td> ".$l[all][Password]." </td>
                <td> ".$l[all][Credit]." </td>
                <td> ".$l[all][Used]." </td>
                <td> ".$l[all][LeftTime]." </td>
                <td> ".$l[all][LeftPercent]." </td>
                <td> ".$l[all][TotalSessions]." </td>
                <td> ".$l[all][Upload]." (".$l[all][Bytes].")</td>
                <td> ".$l[all][Download]." (".$l[all][Bytes].") </td>
        	</tr></thead>
        ";
        while($nt = mysql_fetch_array($res)) {
        echo "<tr>
        	<td> $nt[0] </td>
                <td> $nt[1] </td>
                <td> $nt[2] </td>
                ";
	}

	$sql = "select Value from ".$configValues['CONFIG_DB_TBL_RADCHECK']." where UserName='$username' and Attribute='Max-All-Session'";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");	
        while($nt = mysql_fetch_array($res)) {
	        echo "<td> $nt[0] </td>";
                $credit = $nt[0];
	}

	$sql = "select SUM(AcctSessionTime), COUNT(RadAcctId), SUM(AcctInputOctets), SUM(AcctOutputOctets) from ".$configValues['CONFIG_DB_TBL_RADACCT']." where UserName='$username'";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");	
        while($nt = mysql_fetch_array($res)) {
	        $used = $nt[0];
	        $total_sessions = $nt[1];
	        $total_bytesin = $nt[2];
	        $total_bytesout = $nt[3];
	}

	if ($credit == 0) { 
        	echo "<td> - </td>";
		$remains_per = '-';
	        $remains_t = '-';
	} else {
	        $remains_per = 100 - (($used / $credit) * 100);
	        $remains_t = $credit - $used;
	}

        echo "<td> $used </td>";
        echo "<td> $remains_t </td>";
        echo "<td> $remains_per </td>";
	echo "<td> $total_sessions </td>";
	echo "<td> $total_bytesin </td>";
	echo "<td> $total_bytesout </td>";
        echo "</tr>";
	echo "</table> <br/>";


	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page
	
    $sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADACCT'].".RadAcctId, ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name as hotspot, ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName, ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause, ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." ON ".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid = ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac WHERE UserName='$username';";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
	$numrows = mysql_num_rows($res);	



	
    $sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADACCT'].".RadAcctId, ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name as hotspot, ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName, ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause, ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." ON ".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid = ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac WHERE UserName='$username' ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	setupLinks($pageNum, $maxPage, $orderBy, $orderType,"&username=$username");
	
	if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
		setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType,"&username=$username");
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
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=radacctid&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=radacctid&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][HotSpot]."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=hotspot&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=hotspot&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][Username]."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=username&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=username&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][IPAddress]."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=framedipaddress&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=framedipaddress&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][StartTime]."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=acctstarttime&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=acctstarttime&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][StopTime]."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=acctstoptime&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=acctstoptime&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][TotalTime]."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=acctsessiontime&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=acctsessiontime&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][Upload]." (".$l[all][Bytes].")
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=acctinputoctets&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=acctinputoctets&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][Download]." (".$l[all][Bytes].")
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=acctoutputoctets&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=acctoutputoctets&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][Termination]."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=acctterminatecause&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=acctterminatecause&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][NASIPAddress]."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=nasipaddress&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=nasipaddress&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][Action]." </th

                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[0] </td>
                        <td> $nt[1] </td>
                        <td> $nt[2] </td>
                        <td> $nt[3] </td>
                        <td> $nt[4] </td>
                        <td> $nt[5] </td>
                        <td> ".seconds2time($nt[6], true)." </td>
                        <td> $nt[7] </td>
                        <td> $nt[8] </td>
                        <td> $nt[9] </td>
                        <td> $nt[10] </td>
                        <td> <a href='mng-edit.php?username=$nt[UserName]'> ".$l[all][edit]." </a> </td>
                </tr>";
        }
        echo "</table>";
        mysql_free_result($res);



	} else {
		echo "error: couldn't find this user in the database<br/>";
	}

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
