<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "radacctid";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";
	


	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for hotspot comparison on page: ";
    include('include/config/logging.php');

?>

<?php
	
	include("menu-accounting.php");
	
?>

		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l[Intro][accthotspotcompare.php]; ?></a></h2>
				
				<p>
				</p>



<?php

    include 'library/opendb.php';
	include 'include/common/calcs.php';

	$sql = "select ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name as hotspot, count(distinct(UserName)) as uniqueusers, count(radacctid) as totalhits, avg(AcctSessionTime) as avgsessiontime, sum(AcctSessionTime) as totaltime from ".$configValues['CONFIG_DB_TBL_RADACCT']." join ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." on (".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid like ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac) group by ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name  ORDER BY $orderBy $orderType;;";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='5'>".$l[all][Records]."</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> ".$l[all][HotSpot]."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=hotspot&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=hotspot&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l[all][UniqueUsers]."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=uniqueusers&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=uniqueusers&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l[all][TotalHits]."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=totalhits&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=totalhits&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l[all][AverageTime]."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=avgsessiontime&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=avgsessiontime&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l[all][TotalTime]."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=totaltime&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=totaltime&orderType=desc\"> < </a>
						</th>
                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[0] </td>
                        <td> $nt[1] </td>
                        <td> $nt[2] </td>
                        <td> ".seconds2time($nt[3])." </td>
                        <td> ".seconds2time($nt[4])." </td>
                </tr>";
        }
        echo "</table>";

        mysql_free_result($res);
        include 'library/closedb.php';
?>


<?php
	echo "<br/><br/><br/><center>";
        echo "<img src=\"library/graphs-hotspot-compare-unique-users.php\" /><br/><br/>";
        echo "<img src=\"library/graphs-hotspot-compare-hits.php\" /><br/><br/>";
        echo "<img src=\"library/graphs-hotspot-compare-time.php\" /><br/><br/>";
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
