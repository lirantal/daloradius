<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
	
	include('library/check_operator_perm.php');


	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "id";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";

	//get vendor name passed to us from menu-mng-rad-attributes.php
	isset($_GET['vendor']) ? $vendor = $_GET['vendor'] : $vendor = "%";


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
<link rel="stylesheet" href="css/form-field-tooltip.css" type="text/css" media="screen,projection" />
</head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script src="library/javascript/rounded-corners.js" type="text/javascript"></script>
<script src="library/javascript/form-field-tooltip.js" type="text/javascript"></script>

<script type="text/javascript" src="library/javascript/ajax.js"></script>
<script type="text/javascript" src="library/javascript/ajaxGeneric.js"></script> 
<?php
	include ("menu-mng-rad-attributes.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngradattributeslist.php') ?>
				<h144>&#x2754;</h144></a></h2>
				
				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo t('helpPage','mngradattributeslist') ?>
					<br/>
				</div>
				<br/>


<?php

        
	include 'library/opendb.php';
        include 'include/management/pages_common.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page	
	$sql = "SELECT id, Vendor, Attribute FROM dictionary WHERE Vendor = '$vendor' AND Type <> '';";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";

	$numrows = $res->numRows();

	$sql = "SELECT id, Vendor, Attribute FROM dictionary WHERE Vendor = '$vendor' AND Type <> '' ".
		"ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
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
                setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, "&vendor=$vendor");

        echo " </th></tr>
                                        </thead>

                        ";

        if ($orderType == "asc") {
                $orderTypeNextPage = "desc";
        } else  if ($orderType == "desc") {
                $orderTypeNextPage = "asc";
        }

	echo "<thread> <tr>
		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=id&orderType=$orderTypeNextPage&vendor=$vendor\">
		".t('all','VendorID')."</a>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=vendor&orderType=$orderTypeNextPage&vendor=$vendor\">
		".t('all','VendorName')."</a>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=attribute&orderType=$orderTypeNextPage&vendor=$vendor\">
		".t('all','VendorAttribute')."</a>
		</th>

		</tr> </thread>";
	while($row = $res->fetchRow()) {

		printqn ("<tr>
                                <td> <input type='checkbox' name='vendor[]' value='$row[1]||$row[2]'> $row[0] </td>

				<td>$row[1]</td>

		                <td> <a class='tablenovisit' href='#'
                                onClick='javascript:ajaxGeneric(\"include/management/retVendorAttributeInfo.php\",\"retAttributeInfo\",\"divContainerAttributeInfo\",\"attribute=$row[2]\");return false;'
                                tooltipText='
                                        <a class=\"toolTip\" href=\"mng-rad-attributes-edit.php?vendor=$row[1]&attribute=$row[2]\">
                                                ".t('Tooltip','AttributeEdit')."</a>
                                        <br/><br/>

                                        <div id=\"divContainerAttributeInfo\">
                                                Loading...
                                        </div>
                                        <br/>'
                                >$row[2]</a>
        	                </td>
			</tr>
			");

	}

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='10' align='left'>
        ";
        setupLinks($pageNum, $maxPage, $orderBy, $orderType, "&vendor=$vendor");
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

<script type="text/javascript">
        var tooltipObj = new DHTMLgoodies_formTooltip();
        tooltipObj.setTooltipPosition('right');
        tooltipObj.setPageBgColor('#EEEEEE');
        tooltipObj.setTooltipCornerSize(15);
        tooltipObj.initFormFieldTooltip();
</script>

</body>
</html>
