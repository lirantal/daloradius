<?php
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	include_once('library/config_read.php');
    $log = "visited page: ";

?>

<?php

    include ("menu-billing.php");

?>		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l['Intro']['billrateslist.php']; ?></a></h2>
				
				<p>


<?php

                include 'library/opendb.php';

	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALORATES'].";";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='15'>".$l['Intro']['billrateslist.php']."</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> ".$l['all']['ID']." </th>
                        <th scope='col'> ".$l['all']['Type']." </th>
                        <th scope='col'> ".$l['all']['CardBank']." </th>
                        <th scope='col'> ".$l['all']['Rate']." </th>
                        <th scope='col'> ".$l['all']['Action']." </th>
                </tr> </thread>";
	while($row = $res->fetchRow()) {
                echo "<tr>
                        <td> $row[0] </td>
                        <td> $row[1] </td>
                        <td> $row[2] </td>
                        <td> $row[3] </td>
                        <td> <a href='bill-rates-edit.php?type=$row[1]'> ".$l['all']['edit']." </a> </td>
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
