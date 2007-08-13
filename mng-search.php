<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "id";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";

	isset($_REQUEST['username']) ? $username = $_REQUEST['username'] : $username = "%";	
	$username = str_replace('*', '%', $username);

	include_once('library/config_read.php');
	$log = "visited page: ";
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

	include ("menu-mng-main.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><?php echo "searching for user" ?></h2>
				
				<p>
				<?php echo "searched for user $username" ?>
				</p>

<br/>

<?php


    include 'library/opendb.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file
	
	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page
	$sql = "SELECT distinct(Username) as UserName, value, id FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName like '$username%' GROUP BY UserName";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");	
	$numrows = mysql_num_rows($res);

	$sql = "SELECT distinct(Username) as UserName, value, id FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName like '$username%' GROUP BY UserName ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

	
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
							<th colspan='10'>".$l[all][Records]."</th>
							</tr>
					</thead>
			";

	echo "<thread> <tr>
					<th scope='col'> ".$l[all][ID]. " 
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=id&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=id&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l[all][Username]." 
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=Username&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=Username&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l[all][Password]." 
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=Value&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=Value&orderType=desc\"> < </a>
					</th>
					<th scope='col'> ".$l[all][Action]." </th>
			</tr> </thread>";
	while($nt = mysql_fetch_array($res)) {
			echo "<tr>
					<td> $nt[2] </td>
					<td> $nt[0] </td>
					<td> $nt[1] </td>
					<td> <a href='mng-edit.php?username=$nt[UserName]'> ".$l[all][edit]." </a>
					 <a href='mng-del.php?username=$nt[UserName]'> ".$l[all][del]." </a>
			 </td>

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

