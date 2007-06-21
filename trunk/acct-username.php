<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];


	$username = $_POST['username'];

?>

<?php
	
	include("menu-accounting.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Users Accounting</a></h2>
				
				<p>
				</p>



<?php

        include 'library/config.php';
        include 'library/opendb.php';


	//checking if the username exist in the db
	$sql = "select * FROM radcheck where UserName like '$username'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	if (mysql_num_rows($res) != 0) {		//if the user exist display information

	$credit = 0;

	$sql = "SELECT id, UserName, Value FROM radcheck WHERE UserName LIKE '$username' AND (Attribute='User-Password' or Attribute='Password')";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	echo "
	        <table border='2' class='table1'>
                        <thead>
                                <tr>
                                <th colspan='15'>Statistics</th>
                                </tr>
                        </thead>
	        <thead><tr >
	        <td> Id </td>
	        <td> UserName </td>
                <td> Password </td>
                <td> Credit </td>
                <td> Used </td>
                <td> Left *T </td>
                <td> Left *% </td>
                <td> Total Sessions </td>
                <td> Upload (Bytes) </td>
                <td> Download (Bytes) </td>
        	</tr></thead>
        ";
        while($nt = mysql_fetch_array($res)) {
        echo "<tr>
        	<td> $nt[0] </td>
                <td> $nt[1] </td>
                <td> $nt[2] </td>
                ";
	}

	$sql = "select Value from radcheck where UserName='$username' and Attribute='Max-All-Session'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());	
        while($nt = mysql_fetch_array($res)) {
	        echo "<td> $nt[0] </td>";
                $credit = $nt[0];
	}

	$sql = "select SUM(AcctSessionTime), COUNT(RadAcctId), SUM(AcctInputOctets), SUM(AcctOutputOctets) from radacct where UserName='$username'";
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


	




        $sql = "SELECT radacct.RadAcctId, hotspots.name, radacct.UserName, radacct.FramedIPAddress, radacct.AcctStartTime, radacct.AcctStopTime, radacct.AcctSessionTime, radacct.AcctInputOctets, radacct.AcctOutputOctets, radacct.AcctTerminateCause, radacct.NASIPAddress FROM radacct LEFT JOIN hotspots ON radacct.calledstationid = hotspots.mac WHERE UserName='$username';";
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
                        <th scope='col'> ID </th>
                        <th scope='col'> HotSpot </th>
                        <th scope='col'> Username </th>
                        <th scope='col'> IP Address</th>
                        <th scope='col'> Start Time </th>
                        <th scope='col'> Stop Time </th>
                        <th scope='col'> Total Time </th>
                        <th scope='col'> Upload (Bytes) </th>
                        <th scope='col'> Download (Bytes)</th>
                        <th scope='col'> Termination </th>
                        <th scope='col'> NAS IP Address </th>
                        <th scope='col'> Action </th>
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
                        <td> <a href='mng-edit.php?username=$nt[UserName]'> edit </a> </td>
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
