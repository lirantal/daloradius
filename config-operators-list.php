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
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','configoperatorslist.php') ?>
				<h144>&#x2754;</h144></a></h2>
				
                <div id="helpPage" style="display:none;visibility:visible" >
					<?php echo t('helpPage','configoperatorslist') ?>
					<br/>
				</div>
				<br/>

<?php

    include 'library/opendb.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file
	
	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page
	$sql = "SELECT id, username, firstname, lastname, title FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATORS'];
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";
	
	$numrows = $res->numRows();


	/* we are searching for both kind of attributes for the password, being User-Password, the more
	   common one and the other which is Password, this is also done for considerations of backwards
	   compatibility with version 0.7        */
	
	$sql = "SELECT id, username, password, firstname, lastname, title FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATORS'].
			" ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	/* END */

	echo "<form name='listoperators' method='post' action='config-operators-del.php' >";
	
	echo "<table border='0' class='table1'>\n";
	echo "
					<thead>
                                                        <tr>
                                                        <th colspan='12' align='left'>
                                Select:
                                <a class=\"table\" href=\"javascript:SetChecked(1,'operator_username[]','listoperators')\">All</a>

                                <a class=\"table\" href=\"javascript:SetChecked(0,'operator_username[]','listoperators')\">None</a>
                        <br/>
                                <input class='button' type='button' value='Delete' onClick='javascript:removeCheckbox(\"listoperators\",\"config-operators-del.php\")' />
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
		".t('all','ID'). " 
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=id&orderType=desc\">
			<img src='images/icons/arrow_down.png' alt='<' border='0' /></a>
		</th>
		<th scope='col'>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=Username&orderType=asc\">
			<img src='images/icons/arrow_up.png' alt='>' border='0' /></a>
		".t('all','Username')." 
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=Username&orderType=desc\">
			<img src='images/icons/arrow_down.png' alt='<' border='0' /></a>
		</th>
		<th scope='col'>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=Value&orderType=asc\">
			<img src='images/icons/arrow_up.png' alt='>' border='0' /></a>
		".t('all','Password')." 
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=Value&orderType=desc\">
			<img src='images/icons/arrow_down.png' alt='<' border='0' /></a>
		</th>
		<th scope='col'>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=lastname&orderType=asc\">
			<img src='images/icons/arrow_up.png' alt='>' border='0' /></a>
		Full name
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=lastname&orderType=desc\">
			<img src='images/icons/arrow_down.png' alt='<' border='0' /></a>
		</th>
		<th scope='col'>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=title&orderType=asc\">
			<img src='images/icons/arrow_up.png' alt='>' border='0' /></a>
		Title
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=title&orderType=desc\">
			<img src='images/icons/arrow_down.png' alt='<' border='0' /></a>
		</th>

	</tr> </thread>";

	while($row = $res->fetchRow()) {

		if ( ($row[4] == "") && ($row[3] == "") )
			$fullname = "";
		else 
			$fullname = "$row[4], $row[3]";

		echo "<tr>
			<td> <input type='checkbox' name='operator_username[]' value='$row[1]'>$row[0]</td>
			<td> <a class='tablenovisit' href='config-operators-edit.php?operator_username=$row[1]' title='".
			t('Tooltip','UserEdit')."'>$row[1]</a> </td>
			";
                if ($configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] == "yes") {
                        echo "<td>[Password is hidden]</td>";
                } else {
                        echo "<td>$row[2]</td>";
                }
                echo "
			<td>$fullname</td>
			<td>$row[5]</td>

		</tr>";
	}

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='12' align='left'>
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
