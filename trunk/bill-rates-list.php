<?php
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];


?>

<?php

    include ("menu-billing.php");

?>		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Rates Table</a></h2>
				
				<p>


<?php

        include 'library/config.php';
        include 'library/opendb.php';

	$sql = "SELECT * FROM rates;";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='15'>Rates Table</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> ID </th>
                        <th scope='col'> Type </th>
                        <th scope='col'> Cardbank </th>
                        <th scope='col'> Rate</th>
                        <th scope='col'> Action </th>
                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[0] </td>
                        <td> $nt[1] </td>
                        <td> $nt[2] </td>
                        <td> $nt[3] </td>
                        <td> <a href='bill-rates-edit.php?type=$nt[1]'> edit </a> </td>
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
