<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
	
	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "id";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";



	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";
    include('include/config/logging.php');


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
 
<?php
	include ("menu-mng-rad-nas.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#"><?php echo $l[Intro][mngradnaslist.php] ?></a></h2>
				
				<p>

<?php

        
	include 'library/opendb.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page	
	$sql = "SELECT * FROM nas;";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
	$numrows = mysql_num_rows($res);

	$sql = "SELECT * FROM nas ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;;";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

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
							<th colspan='10'>Records</th>
							</tr>
					</thead>
			";

	echo "<thread> <tr>
					<th scope='col'> ".$l[all][NasID]."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=id&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=id&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l[all][NasIPHost]."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=nasname&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=nasname&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l[all][NasShortname]."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=shortname&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=shortname&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l[all][NasType]."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=type&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=type&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l[all][NasPorts]."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=ports&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=ports&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l[all][NasSecret]."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=secret&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=secret&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l[all][NasCommunity]."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=community&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=community&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l[all][NasDescription]."
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=description&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=description&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l[all][Action]." </th>
			</tr> </thread>";
	while($nt = mysql_fetch_array($res)) {
		echo "<tr>
				<td> $nt[id] </td>
				<td> $nt[nasname] </td>
				<td> $nt[shortname] </td>
				<td> $nt[type] </td>
				<td> $nt[ports] </td>
				<td> $nt[secret] </td>
				<td> $nt[community] </td>
				<td> $nt[description] </td>
				<td> <a href='mng-rad-nas-edit.php?nashost=$nt[nasname]'> ".$l[all][edit]." </a>
					 <a href='mng-rad-nas-del.php?nashost=$nt[nasname]'> ".$l[all][del]." </a>
					 </td>

		</tr>";
	}
	echo "</table>";

	mysql_free_result($res);
	include 'library/closedb.php';
?>


			</p>
			
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
