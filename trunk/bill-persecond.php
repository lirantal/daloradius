<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];


    $hotspot = !empty($_REQUEST['ps-hotspot']) ? $_REQUEST['ps-hotspot'] : '';
	$startdate = $_GET['ps-startdate'];
	$enddate = $_GET['ps-enddate'];

?>

<?php

    include ("menu-billing.php");

?>		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Prepaid Accounting</a></h2>
				
				<p>
				
						Accounting records for hotspot <?php echo $hotspot ?>
				
				</p>

					
<?php

        include 'library/config.php';
        include 'library/opendb.php';

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
                        <th scope='col'> HotSpot </th>
                        <th scope='col'> Last Login Time </th>
                        <th scope='col'> Total Session Time </th>
                        <th scope='col'> Rate </th>
                        <th scope='col'> Billed </th>
                </tr> </thread>";

	// First we search for all users that have been connected AT LEAST ONCE by checking if they appear in the radacct table,
	// then we get their max-all-session attribute to see to how long their time is limited (they're card bank, represented in secs)
	// BUT this will only list rates that have a max-all-session defined for them.

	$sql = "select distinct(radacct.UserName), hotspots.name, radacct.AcctStartTime, Sum(radacct.AcctSessionTime), rates.rate from radacct, rates, hotspots, radcheck where (radacct.Username = radcheck.UserName) and (radcheck.Attribute = 'User-Password' OR radcheck.Attribute = 'Password') and (radacct.AcctStartTime >= '$startdate') and (radacct.AcctStartTime <= '$enddate' ) and (rates.type = 'per second') and (radacct.calledstationid = hotspots.mac) and (hotspots.name like '$hotspot') group by radacct.UserName";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$sum = 0;
	$count = 0;
	$hs = "";	// hotspot name

        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[0] </td>
                        <td> $nt[1] </td>
                        <td> $nt[2] </td>
                        <td> $nt[3] </td>
                        <td> $nt[4] </td>
                        <td>";

			$billed  = $nt[3] * $nt[4];
			echo $billed;

		 echo" </td>
                </tr>";

		$sum = $sum + $billed;
		$count = $count + 1;
		$hs = $nt[1];
		
        }
        echo "</table>";

        mysql_free_result($res);
        include 'library/closedb.php';


	echo "<br/><br/>";
        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='7'>Summary</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> HotSpot </th>
                        <th scope='col'> Total Users </th>
                        <th scope='col'> Total Billed </th>
                </tr> </thread>";

                echo "<tr>
                        <td> $hs </td>
                        <td> $count </td>
                        <td> $sum </td>
			</tr>
			</table>";	






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
