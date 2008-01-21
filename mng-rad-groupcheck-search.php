<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "id";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";


        isset($_REQUEST['groupname']) ? $groupname = $_REQUEST['groupname'] : $groupname = "%";

        $search_groupname = $groupname; //feed the sidebar variables
        $groupname = str_replace('*', '%', $groupname);


	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
 
<?php
	include ("menu-mng-rad-groups.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngradgroupchecksearch.php'] ?>
				<h144>+</h144></a></h2>
				
				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mngradgroupchecksearch'] ?>
					<br/>
				</div>
				<br/>

<?php

	
	include 'library/opendb.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file
	
	//orig: used as method to get total rows - this is required for the pages_numbering.php page
	$sql = "SELECT GroupName, Attribute, op, Value FROM 
".$configValues['CONFIG_DB_TBL_RADGROUPCHECK']." WHERE GroupName LIKE 
'".$dbSocket->escapeSimple($groupname)."%' GROUP BY GroupName";
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();

	$sql = "SELECT GroupName, Attribute, op, Value FROM 
".$configValues['CONFIG_DB_TBL_RADGROUPCHECK']." WHERE GroupName LIKE
'".$dbSocket->escapeSimple($groupname)."%' ORDER BY $orderBy $orderType LIMIT $offset, 
$rowsPerPage;";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";
	
	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	/* END */

	echo "<table border='0' class='table1'>\n";
	echo "
					<thead>
							<tr>
							<th colspan='10'> ".$l['all']['Records']."</th>
							</tr>

                                                        <tr>
                                                        <th colspan='10' align='left'>
                <br/>
        ";

        if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
                setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);

        echo " </th></tr>
                                        </thead>

                        ";


	echo "<thread> <tr>
		<th scope='col'>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=groupname&orderType=asc\">
			<img src='images/icons/arrow_up.png' alt='>' border='0' /></a>
		".$l['all']['Groupname']."
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=groupname&orderType=desc\">
			<img src='images/icons/arrow_down.png' alt='<' border='0' /></a>
		</th>

		<th scope='col'>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=attribute&orderType=asc\">
			<img src='images/icons/arrow_up.png' alt='>' border='0' /></a>
		".$l['all']['Attribute']."
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=attribute&orderType=desc\">
			<img src='images/icons/arrow_down.png' alt='<' border='0' /></a>
		</th>

		<th scope='col'>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=op&orderType=asc\">
			<img src='images/icons/arrow_up.png' alt='>' border='0' /></a>
		".$l['all']['Operator']."
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=op&orderType=desc\">
			<img src='images/icons/arrow_down.png' alt='<' border='0' /></a>
		</th>

		<th scope='col'>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=value&orderType=asc\">
			<img src='images/icons/arrow_up.png' alt='>' border='0' /></a>
		".$l['all']['Value']."
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=value&orderType=desc\">
			<img src='images/icons/arrow_down.png' alt='<' border='0' /></a>
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
					 <a href='mng-rad-groupcheck-del.php?groupname=$row[0]&attribute=$row[1]&value=$row[3]'> ".$l['all']['del']." </a>
					 </td>

		</tr>";
	}

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='10' align='left'>
        ";
        setupLinks($pageNum, $maxPage, $orderBy, $orderType);
        echo "
                                                        </th>
                                                        </tr>
                                        </tfoot>
                ";

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
