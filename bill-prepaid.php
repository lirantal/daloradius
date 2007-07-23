<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');
	$hotspot = "";
	$startdate = "";
	$enddate = "";

	$hotspot = !empty($_REQUEST['hotspot']) ? $_REQUEST['hotspot'] : '';
	$startdate = $_GET['startdate'];
	$enddate = $_GET['enddate'];

?>

<?php

    include ("menu-billing.php");

?>		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><?echo $l[Intro][billprepaid.php]; ?></a></h2>
				
				<p>
					<?php echo $l[captions][acctrecsforhotspot]." <i>".$hotspot."</i>" ?>
				</p>

					
<?php

                include 'library/opendb.php';

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='7'>".$l[all][Records]."</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> ".$l[all][Username]." </th>
                        <th scope='col'> ".$l[all][HotSpot]." </th>
                        <th scope='col'> ".$l[all][CardBank]." <br/> (Max-All-Session attribute) </th>
                        <th scope='col'> ".$l[all][Rate]." </th>
                        <th scope='col'> ".$l[all][Billed]." </th>
                </tr> </thread>";

	// First we search for all users that have been connected AT LEAST ONCE by checking if they appear in the radacct table,
	// then we get their max-all-session attribute to see to how long their time is limited (they're card bank, represented in secs)
	// BUT this will only list rates that have a max-all-session defined for them.

	$sql = "select distinct(".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName), ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name, ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Value, ".$configValues['CONFIG_DB_TBL_DALORATES'].".rate from ".$configValues['CONFIG_DB_TBL_RADACCT'].", ".$configValues['CONFIG_DB_TBL_DALORATES'].", ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].", ".$configValues['CONFIG_DB_TBL_RADCHECK']." where (".$configValues['CONFIG_DB_TBL_RADACCT'].".Username = ".$configValues['CONFIG_DB_TBL_RADCHECK'].".UserName) and (".$configValues['CONFIG_DB_TBL_RADCHECK'].".Attribute = 'Max-All-Session') and (".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid = ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac) and (".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name like '$hotspot') and (".$configValues['CONFIG_DB_TBL_DALORATES'].".cardbank = ".$configValues['CONFIG_DB_TBL_RADCHECK'].".value) and (".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime >= '$startdate') and (".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime <= '$enddate' ) group by ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

	$sum = 0;
	$count = 0;
	$hs = "";	// hotspot name

        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[0] </td>
                        <td> $nt[1] </td>
                        <td> $nt[2] </td>
                        <td> $nt[3] </td>
                        <td>";

			$billed  = $nt[2] * $nt[3];
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
                                <th colspan='7'>".$l[all][Summary]."</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> ".$l[all][HotSpot]." </th>
                        <th scope='col'> ".$l[all][TotalUsers]." </th>
                        <th scope='col'> ".$l[all][TotalBilled]." </th>
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
