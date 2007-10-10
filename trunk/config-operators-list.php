<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	$logDebugSQL = "";

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
<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->
</head>

<?php

    include ("menu-config-operators.php");

?>

		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Operators Listing</a></h2>
				
				<p>
				Listing all Operators in database
				</p>



<?php

    include 'library/opendb.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file
	
	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page
	$sql = "SELECT id, username, firstname, lastname, title FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR'];
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";
	
	$numrows = $res->numRows();


	/* we are searching for both kind of attributes for the password, being User-Password, the more
	   common one and the other which is Password, this is also done for considerations of backwards
	   compatibility with version 0.7        */
	
	$sql = "SELECT id, username, password, firstname, lastname, title FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	setupLinks($pageNum, $maxPage, $orderBy, $orderType);
	
	if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
		setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);
	/* END */
	
	
	echo "<table border='2' class='table1'>\n";
	echo "
					<thead>
							<tr>
							<th colspan='10'>".$l['all']['Records']."</th>
							</tr>
					</thead>
			";

	echo "<thread> <tr>
					<th scope='col'> ".$l['all']['ID']. " 
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=id&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=id&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l['all']['Username']." 
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=Username&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=Username&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l['all']['Password']." 
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=Value&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=Value&orderType=desc\"> < </a>
					</th>




					<th scope='col'> Full name
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=lastname&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=lastname&orderType=desc\"> < </a>
					</th>

					<th scope='col'> Title
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=title&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=title&orderType=desc\"> < </a>
					</th>


					<th scope='col'> ".$l['all']['Action']." </th>
			</tr> </thread>";

	while($row = $res->fetchRow()) {
		echo "<tr>
				<td> $row[0] </td>
				<td> $row[1] </td>
				<td> $row[2] </td>
				<td> $row[4], $row[3] </td>
				<td> $row[5] </td>
				<td> <a href='config-operators-edit.php?operator_username=$row[1]'> ".$l['all']['edit']." </a>
				 <a href='config-operators-del.php?operator_username=$row[1]'> ".$l['all']['del']." </a>
		 </td>

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
