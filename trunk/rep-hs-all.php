<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    include('include/config/logging.php');

?>

<?php

    include ("menu-reports.php");

?>
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><?echo $l[Intro][rephsall.php]; ?></a></h2>
				
				<p>
				<?echo $l[captions][listhotspotsindb]; ?><br/>
				</p>



<?php

        
        include 'library/opendb.php';

        $sql = "SELECT * FROM hotspots;";
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
                        <th scope='col'> ".$l[all][ID]." </th>
                        <th scope='col'> ".$l[all][HotSpot]." </th>
                        <th scope='col'> ".$l[all][MACAddress]." </th>
                        <th scope='col'> ".$l[all][Geocode]." </th>
                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[id] </td>
                        <td> $nt[name] </td>
                        <td> $nt[mac] </td>
                        <td> $nt[geocode] </td>
                        <td> <a href='mng-hs-edit.php?name=$nt[name]'> ".$l[all][edit]." </a>
	                     <a href='mng-hs-del.php?name=$nt[name]'> ".$l[all][del]." </a>
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
