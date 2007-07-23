<?php
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];


?>

<?php

    include ("menu-billing.php");

?>		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l[Intro][billrateslist.php]; ?></a></h2>
				
				<p>


<?php

                include 'library/opendb.php';

	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALORATES'].";";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='15'>".$l[Intro][billrateslist.php]."</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> ".$l[all][ID]." </th>
                        <th scope='col'> ".$l[all][Type]." </th>
                        <th scope='col'> ".$l[all][CardBank]." </th>
                        <th scope='col'> ".$l[all][Rate]." </th>
                        <th scope='col'> ".$l[all][Action]." </th>
                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[0] </td>
                        <td> $nt[1] </td>
                        <td> $nt[2] </td>
                        <td> $nt[3] </td>
                        <td> <a href='bill-rates-edit.php?type=$nt[1]'> ".$l[all][edit]." </a> </td>
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
