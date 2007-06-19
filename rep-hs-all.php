<?php

    include ("menu-reports.php");

?>
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Hotspots Listing</a></h2>
				
				<p>
				Listing hotspots in database<br/>
				</p>



<?php

        include 'library/config.php';
        include 'library/opendb.php';

        $sql = "SELECT * FROM hotspots;";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>Records</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> Account ID </th>
                        <th scope='col'> HotSpots Name </th>
                        <th scope='col'> MAC Address </th>
                        <th scope='col'> Geocode </th>
                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[id] </td>
                        <td> $nt[name] </td>
                        <td> $nt[mac] </td>
                        <td> $nt[geocode] </td>
                        <td> <a href='mng-hs-edit.php?name=$nt[name]'> edit </a>
	                     <a href='mng-hs-del.php?name=$nt[name]'> del </a>
			     </td>

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
