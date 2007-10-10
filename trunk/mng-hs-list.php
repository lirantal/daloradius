<?php
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "id";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";

    


	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
<?php

	include ("menu-mng-main.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#"><?php echo $l['Intro']['mnghslist.php'] ?></a></h2>
				
				<p>


<?php

        
    include 'library/opendb.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page
	$sql = "SELECT id, name, owner, company, type FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].";";
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();

	$sql = "SELECT id, name, owner, company, type FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";
	
	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	setupLinks($pageNum, $maxPage, $orderBy, $orderType);
	
	if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
		setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);
	/* END */
	echo "<br/>";

	echo "<table border='2' class='table1'>\n";
	echo "
					<thead>
							<tr>
							<th colspan='15'>".$l['all']['HotSpots']."</th>
							</tr>
					</thead>
			";

	echo "<thread> <tr>
					<th scope='col'> ".$l['all']['ID']."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=id&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=id&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l['all']['HotSpot']."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=name&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=name&orderType=desc\"> < </a>
					</th>
					<th scope='col'> Owner
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=mac&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=mac&orderType=desc\"> < </a>
					</th>
					<th scope='col'> Company
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=geocode&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=geocode&orderType=desc\"> < </a>
					</th>
					<th scope='col'> HotSpot Type
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=geocode&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=geocode&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l['all']['Action']." </th>
			</tr> </thread>";
	while($row = $res->fetchRow()) {
		echo "<tr>
				<td> $row[0] </td>
				<td> $row[1] </td>
				<td> $row[2] </td>
				<td> $row[3] </td>
				<td> $row[4] </td>
				<td> <a href='mng-hs-edit.php?name=$row[1]'> ".$l['all']['edit']." </a> </td>
				<td> <a href='mng-hs-del.php?name=$row[1]'> ".$l['all']['del']." </a> </td>
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





