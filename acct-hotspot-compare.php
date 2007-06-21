<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

?>

<?php
	
	include("menu-accounting.php");
	
?>

		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Hotspot Comparison</a></h2>
				
				<p>
				</p>



<?php

        include 'library/config.php';
        include 'library/opendb.php';

	$sql = "select hotspots.name, count(distinct(UserName)), count(radacctid), avg(AcctSessionTime), sum(AcctSessionTime) from radacct join hotspots on (radacct.calledstationid like hotspots.mac) group by hotspots.name;";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='5'>Records</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> HotSpot </th>
                        <th scope='col'> Unique Users</th>
                        <th scope='col'> Total Hits </th>
                        <th scope='col'> Average Time </th>
                        <th scope='col'> Total Time </th>
                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[0] </td>
                        <td> $nt[1] </td>
                        <td> $nt[2] </td>
                        <td> $nt[3] </td>
                        <td> $nt[4] </td>
                </tr>";
        }
        echo "</table>";

        mysql_free_result($res);
        include 'library/closedb.php';
?>


<?php
	echo "<br/><br/><br/><center>";
        echo "<img src=\"library/exten-hotspot_compare_unique_users.php\" /><br/><br/>";
        echo "<img src=\"library/exten-hotspot_compare_hits.php\" /><br/><br/>";
        echo "<img src=\"library/exten-hotspot_compare_time.php\" /><br/>";
	echo "</center>";
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
