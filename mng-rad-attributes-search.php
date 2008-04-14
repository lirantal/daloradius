<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
	
	include('library/check_operator_perm.php');


	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "id";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";

	//get attribute name passed to us from menu-mng-rad-attributes.php
	isset($_GET['attribute']) ? $attribute = $_GET['attribute'] : $attribute = "%";


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
	include ("menu-mng-rad-attributes.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngradattributessearch.php'] ?>
				:: <?php if (isset($attribute)) { echo $attribute; } ?><h144>+</h144></a></h2>
				
				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mngradattributessearch'] ?>
					<br/>
				</div>
				<br/>


<?php

        
	include 'library/opendb.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page	
	$sql = "SELECT id, Vendor, Attribute FROM dictionary WHERE Attribute like '%$attribute%' AND Type <> ''".
		" GROUP BY Attribute;";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";

	$numrows = $res->numRows();

	$sql = "SELECT id, Vendor, Attribute FROM dictionary WHERE Attribute like '%$attribute%' AND Type <> '' ".
		" GROUP BY Attribute ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	/* END */


        echo "<form name='listvendorattributes' method='post' action='mng-rad-attributes-del.php'>";

	echo "<table border='0' class='table1'>\n";
	echo "
					<thead>
                                                        <tr>
                                                        <th colspan='10' align='left'>

                                Select:
                                <a class=\"table\" href=\"javascript:SetChecked(1,'vendor[]','listvendorattributes')\">All</a>

                                <a class=\"table\" href=\"javascript:SetChecked(0,'vendor[]','listvendorattributes')\">None</a>
                        <br/>
                                <input class='button' type='button' value='Delete' onClick='javascript:removeCheckbox(\"listvendorattributes\",\"mng-rad-attributes-del.php\")' />
                                <br/><br/>


                ";

        if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
                setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, "&attribute=$attribute");

        echo " </th></tr>
                                        </thead>

                        ";

        if ($orderType == "asc") {
                $orderType = "desc";
        } else  if ($orderType == "desc") {
                $orderType = "asc";
        }

	echo "<thread> <tr>
		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=id&orderType=$orderType&attribute=$attribute\">
		".$l['all']['VendorID']."</a>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=vendor&orderType=$orderType&attribute=$attribute\">
		".$l['all']['VendorName']."</a>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=attribute&orderType=$orderType&attribute=$attribute\">
		".$l['all']['VendorAttribute']."</a>
		</th>

		</tr> </thread>";
	while($row = $res->fetchRow()) {
		echo "<tr>
                                <td> <input type='checkbox' name='vendor[]' value='$row[1]||$row[2]'> $row[0] </td>
				<td> <a class='tablenovisit' href='mng-rad-attributes-list.php?vendor=$row[1]'>$row[1]</a></td>
				<td> $row[2] </td>
		</tr>";
	}

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='10' align='left'>
        ";
        setupLinks($pageNum, $maxPage, $orderBy, $orderType, "&attribute=$attribute");
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
