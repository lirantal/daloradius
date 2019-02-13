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
	isset($_GET['orderBy']) ? $orderBy = $_GET['orderBy'] : $orderBy = "radacctid";
	isset($_GET['orderType']) ? $orderType = $_GET['orderType'] : $orderType = "asc";


	isset($_GET['fields']) ? $where = $_GET['fields'] : $where = "";
	isset($_GET['sqlfields']) ? $sqlfields = $_GET['sqlfields'] : $sqlfields = "";
	isset($_GET['operator']) ? $op = $_GET['operator'] : $op = "=";
	isset($_GET['where_field']) ? $value = $_GET['where_field'] : $value = "";
	isset($_GET['startdate']) ? $startdate = $_GET['startdate'] : $startdate = "";
	isset($_GET['enddate']) ? $enddate = $_GET['enddate'] : $enddate = "";


	//feed the sidebar variables
	$accounting_custom_startdate = $startdate;
	$accounting_custom_enddate = $enddate;
	$accounting_custom_value = $value;


	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for all accounting records on page: ";

?>

<?php
	
	include("menu-accounting-custom.php");
	
?>

		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','acctcustomquery.php')?>
		<h144>&#x2754;</h144></a></h2>
				
		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','acctcustomquery') ?>
			<br/>
		</div>
		<br/>



<?php

		include 'library/opendb.php';
		include 'include/management/pages_common.php';	
		include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file


		if ($op == "LIKE") {						// if the op is LIKE then the SQL syntax uses % for pattern matching
			$value = "%$value%";					// and we sorround the $value with % as a wildcard
		}

		// let's sanitize the values passed to us:
		$where = $dbSocket->escapeSimple($where);
		$operator = $dbSocket->escapeSimple($operator);
		$value = $dbSocket->escapeSimple($value);
		$startdate = $dbSocket->escapeSimple($startdate);
		$enddate = $dbSocket->escapeSimple($enddate);

		// since we need to span through pages, which we do using GET queries I can't rely on this page
		// to be processed through POST but rather using GET only (with the current design anyway).
		// For this reason, I need to build the GET query which I will later use in the page number's links

		$getFields = "";		
		$counter = 0;
		foreach ($sqlfields as $elements) {
			$getFields .= "&sqlfields[$counter]=$elements";
			$counter++;
		}

		// we should also sanitize the array that we will be passing to this page in the next query
		$getFields = $dbSocket->escapeSimple($getFields);


		$getQuery = "";
		$getQuery .= "&fields=$where&operator=$op&where_field=$value";
		$getQuery .= "&startdate=$startdate&enddate=$enddate";


	
		$select = implode(",", $sqlfields);
		// sanitizing the array passed to us in the get request
		$select = $dbSocket->escapeSimple($select);


		$sql = "SELECT $select FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." WHERE ($where $op '$value') AND (AcctStartTime>'$startdate'
			 AND AcctStartTime<'$enddate');";
		$res = $dbSocket->query($sql);
		$numrows = $res->numRows();


		$sql = "SELECT $select FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." WHERE ($where $op '$value') AND (AcctStartTime>'$startdate'
			AND AcctStartTime<'$enddate') ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
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
							<th colspan='25'>".t('all','Records')."</th>
							</tr>

                                                        <tr>
                                                        <th colspan='25' align='left'>
                <br/>
        ";

        if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
                setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $getFields, $getQuery);

        echo " </th></tr>
                                        </thead>

                        ";


	// building the dybamic table list fields
	echo "<thread> <tr>";
	foreach ($sqlfields as $value) {
		echo "<th scope='col'> $value   </th>";
	} //foreach $sqlfields
	echo "</tr> </thread>";


	// inserting the values of each field from the database to the table
	while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
		echo "<tr>";
		foreach ($sqlfields as $value) {
			echo "<td> " . $row[$value] . "</td>";
		}
		echo "</tr>";
	}

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='25' align='left'>
        ";
        setupLinks($pageNum, $maxPage, $orderBy, $orderType, $getFields, $getQuery);
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
