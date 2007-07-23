<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
	
	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');

	$hotspot = $_POST['hotspot'];



?>

<?php
	
	include("menu-accounting.php");
	
?>
		
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l[Intro][accthotspot.php]; ?></a></h2>
				
				<p>
				</p>



<?php

                include 'library/opendb.php';

        $sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADACCT'].".RadAcctId, ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name, ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName, ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause, ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." ON ".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid = ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac WHERE ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name='$hotspot'";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

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
		<th scope='col'> ".$l[all][StartTime]." </TH>
		<th scope='col'> ".$l[all][StopTime]." </th>
		<th scope='col'> ".$l[all][TotalTime]." </th>
		<th scope='col'> ".$l[all][Upload]." (".$l[all][Bytes].") </th>
		<th scope='col'> ".$l[all][Download]." (".$l[all][Bytes].") </th>
		<th scope='col'> ".$l[all][Termination]." </th>
		<th scope='col'> ".$l[all][NASIPAddress]." </th>
		<th scope='col'> ".$l[all][Action]." </th>
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
