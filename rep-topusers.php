<?php

    include ("menu-reports.php");

?>

<?php

	if (isset($_POST['limit']))
		$limit = $_POST['limit'];
	if (isset($_POST['order']))		
		$order = $_POST['order'];

?>
		
		
		
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><?php echo $l[Intro][reptopusers.php]; ?></a></h2>
				
				<p>
				<?php echo $l[captions][recordsfortopusers]." ".$order ?> <br/>
				</p>



<?php

        
        include 'library/opendb.php';

	$sql = "SELECT distinct(radacct.UserName), ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime,
sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime) as Time, sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets) as Upload,sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets) as Download, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause, ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress, sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets+".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets) as Bandwidth FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." group by UserName order by $order desc limit $limit";

	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>".$l[all][Records]."</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> ".$l[all][Username]." </th>
                        <th scope='col'> ".$l[all][IPAddress]."</th>
                        <th scope='col'> ".$l[all][StartTime]." </th>
                        <th scope='col'> ".$l[all][StopTime]." </th>
                        <th scope='col'> ".$l[all][TotalTime]." </th>
                        <th scope='col'> ".$l[all][Upload]." (".$l[all][Bytes].") </th>
                        <th scope='col'> ".$l[all][Download]." (".$l[all][Bytes].") </th>
                        <th scope='col'> ".$l[all][Termination]." </th>
                        <th scope='col'> ".$l[all][NASIPAddress]." </th>
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
