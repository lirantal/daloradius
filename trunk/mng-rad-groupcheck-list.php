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
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
 
 
<?php
	include ("menu-mng-rad-groupcheck.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngradgroupchecklist.php'] ?></a></h2>
				
                                <div id="helpPage" style="display:none;visibility:visible" >				
					<?php echo $l['helpPage']['mngradgroupchecklist'] ?>
				</div>

<?php

	
	include 'library/opendb.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page
	$sql = "select GroupName, Attribute, op, Value FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK'].";";
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();

	$sql = "select GroupName, Attribute, op, Value FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK']." ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
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
							<th colspan='10'>".$l['all']['Records']."</th>
							</tr>
					</thead>
			";

	echo "<thread> <tr>
					<th scope='col'> ".$l['all']['Groupname']."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=groupname&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=groupname&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l['all']['Attribute']."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=attribute&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=attribute&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l['all']['Operator']."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=op&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=op&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l['all']['Value']."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=value&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=value&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l['all']['Action']." </th>						
			</tr> </thread>";
	while($row = $res->fetchRow()) {
		echo "<tr>
				<td> $row[0] </td>
				<td> $row[1] </td>
				<td> $row[2] </td>						
				<td> $row[3] </td>						
				<td> <a href='mng-rad-groupcheck-edit.php?groupname=$row[0]&value=$row[3]'> ".$l['all']['edit']." </a>
					 <a href='mng-rad-groupcheck-del.php?groupname=$row[0]&value=$row[3]'> ".$l['all']['del']." </a>
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
