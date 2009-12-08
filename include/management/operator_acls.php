<?php
/*********************************************************************
* Name: operator_acls.php
* Author: Liran tal <liran.tal@gmail.com>
* 
* Used to provide a listing of the available pages which
* operators may have access to as taken from the operators table in
* the database
*
*********************************************************************/


function drawOperatorACLs($operator_id = "") {

	include 'library/opendb.php';
	
	echo "
		<table border='2' class='table1' width='600'>
		<thead>
						<tr>
						<th colspan='10'>Permission to access pages</th>
						</tr>
		</thead>
		<tbody>
						<tr>
							<td> <b> Category </b> </td>
							<td> <b> Section </b> </td>
							<td> <b> Page </b> </td>
							<td> <b> Access </b> </td>
						</tr>
		</tbody>
		";
	
	
	$sql = "SELECT DISTINCT(".$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES'].".file), ".
			$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES'].".category, ".
			$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES'].".section, ".
			$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'].".access ".
			" FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES'].
			" LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'].
			" ON ".
			$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES'].".file = ".
			$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'].".file ";
			
	if ($operator_id) {
		/*
		$sql .= " WHERE ".
			$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'].".operator_id = ".
			$operator_id;
		*/
		$sql .= " AND ".
			$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'].".operator_id = ".
			$operator_id;
	}
	
	$sql .= " ORDER BY category, section ASC ";
	
	$res = $dbSocket->query($sql);
	
	while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
		
		$file = $row['file'];
		$category = $row['category'];
		$section = $row['section'];
		$access = $row['access'];
		
		$selectedYes = "";
		$selectedNo = "";
		
		if ($access == 0)
			$selectedNo = "selected";
		
		if ($access == 1)
			$selectedYes = "selected";
		
		echo "<tr>
				<td>
					".$category."
				</td>
				<td>
					".$section."
				</td>
				<td>
					".$file."
				</td>
			";

		echo "<td>
			  <select name='ACL_$file'>
			  <option value='1' $selectedYes > Enabled
			  <option value='0' $selectedNo > Disabled
			  </select>
			  </td>
		";
		

		echo "</tr>";
	}
	
	echo "</table>";
	
		
	include 'library/closedb.php';
}


?>

