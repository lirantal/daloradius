<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "radacctid";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";
	


	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for hotspot comparison on page: ";

?>

<?php
        include_once ("library/tabber/tab-layout.php");
?>

<?php
	
	include("menu-accounting-hotspot.php");
	
?>

		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><? echo $l['Intro']['accthotspotcompare.php']; ?>
		<h144>+</h144></a></h2>
				
		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo $l['helpPage']['accthotspotcompare'] ?>
			<br/>
		</div>
		<br/>


<div class="tabber">

     <div class="tabbertab" title="Account Info">
        <br/>


<?php

	include 'library/opendb.php';
	include 'include/common/calcs.php';

	$sql = "SELECT ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
		".name AS hotspot, count(distinct(UserName)) AS uniqueusers, count(radacctid) AS totalhits, ".
		" avg(AcctSessionTime) AS avgsessiontime, sum(AcctSessionTime) AS totaltime, ".
		" avg(AcctInputOctets) AS avgInputOctets, sum(AcctInputOctets) AS sumInputOctets, ".
		" avg(AcctOutputOctets) AS avgOutputOctets, sum(AcctOutputOctets) AS sumOutputOctets ".
		" FROM ".
		$configValues['CONFIG_DB_TBL_RADACCT']." JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
		" on (".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid LIKE ".
		$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac) GROUP BY ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
		".name  ORDER BY $orderBy $orderType;";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";


        echo "<table border='0' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='5'>".$l['all']['Records']."</th>
                                </tr>
                        </thead>
                ";

	if ($orderType == "asc") {
			$orderType = "desc";
	} else  if ($orderType == "desc") {
			$orderType = "asc";
	}
	
	echo "<thread> <tr>
                <th scope='col'> 
			<br/>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=hotspot&orderType=$orderType\">
			".$l['all']['HotSpot']."</a>
			</th>
			<th scope='col'> 
			<br/>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=uniqueusers&orderType=$orderType\">
			".$l['all']['UniqueUsers']."</a>
			</th>
			<th scope='col'> 
			<br/>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=totalhits&orderType=$orderType\">
			".$l['all']['TotalHits']."</a>
			</th>
			<th scope='col'> 
			<br/>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=avgsessiontime&orderType=$orderType\">
			".$l['all']['AverageTime']."</a>
			</th>
			<th scope='col'> 
			<br/>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=totaltime&orderType=$orderType\">
			".$l['all']['TotalTime']."</a>
			</th>
        </tr> </thread>";
	while($row = $res->fetchRow()) {
                echo "<tr>
                        <td> $row[0] </td>
                        <td> $row[1] </td>
                        <td> $row[2] </td>
                        <td> ".seconds2time($row[3])." </td>
                        <td> ".seconds2time($row[4])." </td>
                </tr>";
        }
        echo "</table>";

        include 'library/closedb.php';
?>

	</div>

     <div class="tabbertab" title="Graph">
        <br/>

<?php
	echo "<br/><br/><br/><center>";
        echo "<img src=\"library/graphs-hotspot-compare-unique-users.php\" /><br/><br/>";
        echo "<img src=\"library/graphs-hotspot-compare-hits.php\" /><br/><br/>";
        echo "<img src=\"library/graphs-hotspot-compare-time.php\" /><br/><br/>";
	echo "</center>";
?>

	</div>

</div>


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
