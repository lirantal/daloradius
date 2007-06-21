<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

?>


<?php
	
	include("menu-accounting.php");
	
?>
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Active Records Accounting</a></h2>
				
				<p>
				</p>



<?php

        include 'library/config.php';
        include 'library/opendb.php';
        include 'library/datediff.php';

	$currdate = date("j M Y");

	$sql = "select distinct(radacct.UserName), radcheck.attribute, radcheck.Value, sum(radacct.AcctSessionTime) from radacct, radcheck where (radacct.Username = radcheck.UserName) and (radcheck.Attribute = 'Max-All-Session' or radcheck.Attribute = 'Expiration') group by radacct.UserName";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='7'>Records</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> Username </th>
                        <th scope='col'> Attribute </th>
                        <th scope='col'> Max Time / Expiration</th>
                        <th scope='col'> Used Time </th>
                        <th scope='col'> Status </th>
                        <th scope='col'> Usage </th>
                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
		$status="Active";

		if ($nt[1] == "Expiration") {		
			if (datediff('d', $nt[2], '$currdate', false) > 0) {
				$status = "Expired";
			}
		} 


		if ($nt[1] == "Max-All-Session") {		
			if ($nt[3] >= $nt[2]) {
				$status = "End";
			}
		}

                echo "<tr>
                        <td> $nt[0] </td>
                        <td> $nt[1] </td>
                        <td> $nt[2] </td>
                        <td> $nt[3] </td>
                        <td> $status </td>
			<td> ";

		if ($nt[1] == "Expiration") {		
			echo datediff('d', $nt[2], '27 Nov 2006', false);
			echo " days since expired";
//			echo date("j M Y");
		} 

		if ($nt[1] == "Max-All-Session") {		
			echo $nt[2] - $nt[3];
			echo " left on credit";
		} 


		echo "	</td>
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
