<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "username";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";



	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for active accounting records on page: ";
    include('include/config/logging.php');

?>


<?php
	
	include("menu-accounting.php");
	
?>
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l[Intro][acctactive.php]; ?></a></h2>
				
				<p>
				</p>



<?php

	include 'library/opendb.php';
    include 'library/datediff.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file
	$currdate = date("j M Y");

	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page

	$sql = "select distinct(".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName) as username, ".$configValues['CONFIG_DB_TBL_RADCHECK'].".attribute as attribute, ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Value maxtimeexpiration, sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime) as usedtime from ".$configValues['CONFIG_DB_TBL_RADACCT'].", ".$configValues['CONFIG_DB_TBL_RADCHECK']." where (".$configValues['CONFIG_DB_TBL_RADACCT'].".Username = ".$configValues['CONFIG_DB_TBL_RADCHECK'].".UserName) and (".$configValues['CONFIG_DB_TBL_RADCHECK'].".Attribute = 'Max-All-Session' or ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Attribute = 'Expiration') group by ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName;";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
	$numrows = mysql_num_rows($res);	

	
	$sql = "select distinct(".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName) as username, ".$configValues['CONFIG_DB_TBL_RADCHECK'].".attribute as attribute, ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Value maxtimeexpiration, sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime) as usedtime from ".$configValues['CONFIG_DB_TBL_RADACCT'].", ".$configValues['CONFIG_DB_TBL_RADCHECK']." where (".$configValues['CONFIG_DB_TBL_RADACCT'].".Username = ".$configValues['CONFIG_DB_TBL_RADCHECK'].".UserName) and (".$configValues['CONFIG_DB_TBL_RADCHECK'].".Attribute = 'Max-All-Session' or ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Attribute = 'Expiration') group by ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName  ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	setupLinks($pageNum, $maxPage, $orderBy, $orderType,"&username=$username&startdate=$startdate&enddate=$enddate");
	
	if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
		setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType,"&username=$username&startdate=$startdate&enddate=$enddate");
	/* END */
	echo "<br/>";

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='7'>".$l[all][Records]."</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> ".$l[all][Username]."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=username&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=username&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l[all][Attribute]."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=attribute&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=attribute&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l[all][MaxTimeExpiration]."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=maxtimeexpiration&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=maxtimeexpiration&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l[all][UsedTime]."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=usedtime&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=usedtime&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l[all][Status]." </th>
                        <th scope='col'> ".$l[all][Usage]." </th>
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
