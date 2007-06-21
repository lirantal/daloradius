<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	$username = $_POST['username'];
	$startdate = $_POST['startdate'];
	$enddate = $_POST['enddate'];

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

        $sql = "SELECT radacct.RadAcctId, hotspots.name, radacct.UserName, radacct.FramedIPAddress, radacct.AcctStartTime, radacct.AcctStopTime, radacct.AcctSessionTime, radacct.AcctInputOctets, radacct.AcctOutputOctets, radacct.AcctTerminateCause, radacct.NASIPAddress FROM radacct LEFT JOIN hotspots ON radacct.calledstationid = hotspots.mac WHERE AcctStartTime>'$startdate' and AcctStartTime<'$enddate' and UserName like '$username'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());


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
                        <th scope='col'> Download (Bytes) </th>
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

	        $counter++;
	        $session_seconds += $nt[5];
//	        $session_minutes = int($session_seconds / 60);
	        $bytesin= $bytesin + $nt[6];
	        $bytesout= $bytesout + $nt[7];
//	        $megabytesin = int($bytesin / 1000000);
//	        $megabytesout = int($bytesout / 1000000);
	
        }
        echo "</table>";

        mysql_free_result($res);
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
