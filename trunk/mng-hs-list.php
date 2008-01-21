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

	include ("menu-mng-main.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mnghslist.php'] ?>
				<h144>+</h144></a></h2>
				
				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mnghslist'] ?>
					<br/>
				</div>
				<br/>


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
	/* END */

    
	echo "<form name='listallhotspots' method='post' action='mng-hs-del.php'>";

	echo "<table border='0' class='table1'>\n";
	echo "
					<thead>
							<tr>
							<th colspan='15'>".$l['all']['HotSpots']."</th>
							</tr>

                                                        <tr>
                                                        <th colspan='10' align='left'>
                                Select:
                                <a class=\"table\" href=\"javascript:SetChecked(1,'name[]','listallhotspots')\">All</a> 
                                
                                <a class=\"table\" href=\"javascript:SetChecked(0,'name[]','listallhotspots')\">None</a>
	                 <br/>
                                <input class='button' type='button' value='Delete' onClick='javascript:removeHotspotCheckbox(\"listallhotspots\")' />
                                <br/><br/>

        ";

        if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
                setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);

        echo " </th></tr>
                                        </thead>

                        ";

	echo "<thread> <tr>
		<th scope='col'>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=id&orderType=asc\">
			<img src='images/icons/arrow_up.png' alt='>' border='0' /></a>
		".$l['all']['ID']."
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=id&orderType=desc\">
			<img src='images/icons/arrow_down.png' alt='<' border='0' /></a>
		</th>

		<th scope='col'> 
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=name&orderType=asc\">
			<img src='images/icons/arrow_up.png' alt='>' border='0' /></a>
		".$l['all']['HotSpot']."
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=name&orderType=desc\">
			<img src='images/icons/arrow_down.png' alt='<' border='0' /></a>
		</th>

		<th scope='col'> 
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=mac&orderType=asc\">
			<img src='images/icons/arrow_up.png' alt='>' border='0' /></a>
		".$l['FormField']['mnghslist.php']['Owner']."
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=mac&orderType=desc\">
			<img src='images/icons/arrow_down.png' alt='<' border='0' /></a>
		</th>

		<th scope='col'>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=geocode&orderType=asc\">
			<img src='images/icons/arrow_up.png' alt='>' border='0' /></a>
		".$l['FormField']['mnghslist.php']['Company']."
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=geocode&orderType=desc\">
			<img src='images/icons/arrow_down.png' alt='<' border='0' /></a>
		</th>

		<th scope='col'>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=geocode&orderType=asc\">
			<img src='images/icons/arrow_up.png' alt='>' border='0' /></a>
		 ".$l['FormField']['mnghslist.php']['HotspotType']."
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=geocode&orderType=desc\">
			<img src='images/icons/arrow_down.png' alt='<' border='0' /></a>
		</th>

	</tr> </thread>";
	while($row = $res->fetchRow()) {
		echo "<tr>
                                <td> <input type='checkbox' name='name[]' value='$row[1]'> $row[0] </td>
				<td> <a class='tablenovisit' href='mng-hs-edit.php?name=$row[1]' title='".$l['Tooltip']['HotspotEdit']."'> $row[1] </a> </td>
				<td> $row[2] </td>
				<td> $row[3] </td>
				<td> $row[4] </td>
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
        echo "</form>";

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





