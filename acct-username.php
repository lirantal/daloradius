<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];


	$username = $_POST['username'];

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


	//checking if the username exist in the db
	$sql = "select * FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." where UserName like '$username'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	if (mysql_num_rows($res) != 0) {		//if the user exist display information

	$credit = 0;

	$sql = "SELECT id, UserName, Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName LIKE '$username' AND (Attribute='User-Password' or Attribute='Password')";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

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
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());	
        while($nt = mysql_fetch_array($res)) {
	        echo "<td> $nt[0] </td>";
                $credit = $nt[0];
	}

	$sql = "select SUM(AcctSessionTime), COUNT(RadAcctId), SUM(AcctInputOctets), SUM(AcctOutputOctets) from ".$configValues['CONFIG_DB_TBL_RADACCT']." where UserName='$username'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());	
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


	
    $sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADACCT'].".RadAcctId, ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name, ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName, ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause, ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." ON ".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid = ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac WHERE UserName='$username';";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='15'>Records</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
		<th scope='col'> ".$l[all][ID]." </th>
		<th scope='col'> ".$l[all][HotSpot]." </th>
		<th scope='col'> ".$l[all][Username]." </th>
		<th scope='col'> ".$l[all][IPAddress]."</th>
		<th scope='col'> ".$l[all][StartTime]." </th>
		<th scope='col'> ".$l[all][StopTime]." </th>
		<th scope='col'> ".$l[all][TotalTime]." </th>
		<th scope='col'> ".$l[all][Upload]." (".$l[all][Bytes].") </th>
		<th scope='col'> ".$l[all][Download]." (".$l[all][Bytes].") </th>
		<th scope='col'> ".$l[all][Termination]." </th>
		<th scope='col'> ".$l[all][NASIPAddress]." </th>
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
                        <td> $nt[6] </td>
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
