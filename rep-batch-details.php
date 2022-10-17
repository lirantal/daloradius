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

	//include('library/check_operator_perm.php');


	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "id";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";
	
	isset($_GET['batch_name']) ? $batch_name = $_GET['batch_name'] : $batch_name = 0;

	
	include_once('library/config_read.php');
	$log = "visited page: ";
	$logQuery = "performed query for batch [$batch_name] on page: ";
	$logDebugSQL = "";

	if ($batch_name)
		$batch_name_details = $batch_name;
?>

<?php

    include ("menu-reports-batch.php");
  	
?>	


	<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"  onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','repbatchdetails.php'); ?>
		<h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','repbatchdetails') ?>
			<br/>
		</div>


<?php

	include 'include/management/pages_common.php';
	include 'library/opendb.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file
	
	$batch_id = 0;
	
	if ($batch_name) {

		$batch_name = $dbSocket->escapeSimple($batch_name);
		
		$sql = "SELECT ".
				$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".id ".
				" FROM ".
				$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].
				" WHERE ".
				$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".batch_name = '$batch_name' ".
				" GROUP by ".$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".batch_name LIMIT 1";
		$res = $dbSocket->query($sql);
		$numrows = $res->numRows();
		$logDebugSQL .= $sql . "\n";
		
		if ($numrows > 0) {
			$row = $res->fetchRow();
			$batch_id = $row[0];
		}
	}

	

	$_SESSION['reportParams']['batch_id'] = $batch_id;
	
	$sql = "SELECT ".
			$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".id,".
			$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".batch_name,".
			$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".batch_description,".
			$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".batch_status,".
			
			"COUNT(DISTINCT(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".id)) as total_users,".
			"COUNT(DISTINCT(".$configValues['CONFIG_DB_TBL_RADACCT'].".username)) as active_users,".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planname,".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".plancost,".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".plancurrency,".
			$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name as HotspotName,".
			
			
			$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".creationdate,".
			$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".creationby,".
			$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".updatedate,".
			$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".updateby ".
			" FROM ".$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].
			" LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
			" ON ".
			"(".$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".id = ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".batch_id) ".

			" LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
			" ON ".
			"(".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planname = ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planname) ".

			" LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
			" ON ".
			"(".$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".hotspot_id = ".
			$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".id) ".
			
			" LEFT JOIN ".$configValues['CONFIG_DB_TBL_RADACCT'].
			" ON ".
			"(".$configValues['CONFIG_DB_TBL_RADACCT'].".username = ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username) ".
			
			" WHERE ".$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".batch_name = '$batch_name' ".
			" GROUP by ".$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".batch_name ";
			
	
		$res = $dbSocket->query($sql);
		
		$logDebugSQL .= $sql . "\n";
		
		echo "<table border='0' class='table1'>\n";
		echo "
						<thead>
								<tr>
								<th colspan='10' align='left'> 
								
	  	              			<input class='button' type='button' value='Download Invoice'onClick=\"javascript:window.location.href='include/common/notificationsBatchDetails.php?batch_name=$batch_name&destination=download'\"/>
	  	              			<input class='button' type='button' value='Email Invoice to Business/Hotspot'onClick=\"javascript:window.location.href='include/common/notificationsBatchDetails.php?batch_name=$batch_name&destination=email'\"/>
	  	              			<br/>
								<input class='button' type='button' value='Total Users CSV Export'
									onClick=\"javascript:window.location.href='include/management/fileExport.php?reportFormat=csv&reportType=reportsBatchTotalUsers'\"
								/>
								<br/><br/>
			";
		
		echo "
				</th>
				</tr>
				</thead>
		
			<thread> <tr>
			<th scope='col'> 
			".t('all','BatchName')."
			</th>
	
			<th scope='col'> 
			".t('all','HotSpot')."
			</th>
	
			<th scope='col'> 
			".t('all','BatchStatus')."
			</th>
			
			<th scope='col'> 
			".t('all','TotalUsers')."
			</th>
	
			<th scope='col'> 
			".t('all','ActiveUsers')."
			</th>
	
			<th scope='col'> 
			".t('all','PlanName')."
			</th>
	
			<th scope='col'> 
			".t('all','PlanCost')."
			</th>
	
			<th scope='col'> 
			".t('all','BatchCost')."
			</th>
	
			<th scope='col'> 
			".t('all','CreationDate')."
			</th>
	
			<th scope='col'> 
			".t('all','CreationBy')."
			</th>
	
			</tr> </thread>";
	
			
		$active_users_per = 0;
		$total_users = 0;
		$active_users = 0;
		$batch_cost = 0;
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
	
			$hotspot_name = $row['HotspotName'];
			$batch_status = $row['batch_status'];
			$plancost = $row['plancost'];
			$total_users = $row['total_users'];
			$active_users = $row['active_users'];
			$batch_cost = ($active_users * $plancost);
			$plan_currency = $row['plancurrency'];
			
			echo "
				<tr>
					<td>";
	
			printqn("
					<a class='tablenovisit' href='#'
						onclick='javascript:return false;'
						tooltipText='
									<div id=\"divContainerUserInfo\">
										<b>".t('all','batchDescription')."</b>:<br/><br/>
										{$row['batch_description']}
									</div>
									<br/>
									'
				>{$row['batch_name']}</a>
				</td>
			");
			
			echo "
			
					<td>".$hotspot_name."
						
					</td>
			
					<td>".$batch_status."
						
					</td>
					
					<td>".$total_users."
						
					</td>
	
					<td>".$active_users."
						
					</td>
	
					<td>".
						$row['planname']."
					</td>
	
					<td>".$plancost."
					</td>
	
					<td>".$batch_cost."
					</td>
					
					<td>".
						$row['creationdate']."
					</td>
	
					<td>".
						$row['creationby']."
					</td>
	
	
				</tr>
			";
				
		}
		
		echo "
						<tfoot>
								<tr>
								<th colspan='10' align='left'> 

								</th>
								</tr>
						</tfoot>
			";
	
		echo "</table>";
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	// setup php session variables for exporting
	$_SESSION['reportTable'] = "";
	//reportQuery is assigned below to the SQL statement  in $sql
	$_SESSION['reportQuery'] = "";
	$_SESSION['reportType'] = "reportsBatchActiveUsers";
	
	//orig: used as method to get total rows - this is required for the pages_numbering.php page
	$sql = "SELECT ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".id,".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username,".
			$configValues['CONFIG_DB_TBL_RADACCT'].".acctstarttime,".
			$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".batch_name ".
			
			" FROM ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].",".
			$configValues['CONFIG_DB_TBL_RADACCT'].",".
			$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].
			
			" WHERE ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".batch_id = ".
			$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".id".
			" AND ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".batch_id = $batch_id ".
			" AND ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username = ".
			$configValues['CONFIG_DB_TBL_RADACCT'].".username".

			" GROUP by ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username ".
			" ORDER BY $orderBy $orderType ,".$configValues['CONFIG_DB_TBL_RADACCT'].".radacctid ASC ";

	// assigning the session reportQuery
	$_SESSION['reportQuery'] = $sql;
	
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();
	$logDebugSQL .= $sql . "\n";


	$sql = "SELECT ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".id,".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username,".
			$configValues['CONFIG_DB_TBL_RADACCT'].".acctstarttime,".
			$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".batch_name ".
			
			" FROM ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].",".
			$configValues['CONFIG_DB_TBL_RADACCT'].",".
			$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].
			
			" WHERE ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".batch_id = ".
			$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".id".
			" AND ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".batch_id = $batch_id ".
			" AND ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username = ".
			$configValues['CONFIG_DB_TBL_RADACCT'].".username".

			" GROUP by ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username ".
			" ORDER BY $orderBy $orderType ,".$configValues['CONFIG_DB_TBL_RADACCT'].".radacctid ASC ".
			" LIMIT $offset, $rowsPerPage;";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";


	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	/* END */

	echo "<form name='listallusers' method='get' action='mng-del.php' >";

	echo "<table border='0' class='table1'>\n";
	echo "
					<thead>
							<tr>
							<th colspan='10' align='left'> 
								<input class='button' type='button' value='Active Users CSV Export'
									onClick=\"javascript:window.location.href='include/management/fileExport.php?reportFormat=csv'\"
								/>
							<br/><br/>
		";


	/* drawing the number links */
	if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
		setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);

	echo "
			</th>
			</tr>
			</thead>
			";
	  $curOrderType = $orderType;
        if ($orderType == "asc") {
                $orderType = "desc";
        } else  if ($orderType == "desc") {
                $orderType = "asc";
        }
	
	echo "<thread> <tr>
		<th scope='col'> 
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=id&orderType=$orderType\">
		".t('all','BatchName')."</a>
		</th>

		<th scope='col'> 
		".t('all','Username')."
		</th>

		<th scope='col'> 
		".t('all','StartTime')."
		</th>

		</tr> </thread>";

		
	$active_users_per = 0;
	$total_users = 0;
	$active_users = 0;
	$batch_cost = 0;
	while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {

		$username = $row['username'];
		$acctstarttime = $row['acctstarttime'];
		$batch_name = $row['batch_name'];
		
		echo "
				<td>".$batch_name."
				</td>

				<td>".$username."
				</td>

				<td>".$acctstarttime."
				</td>

			</tr>
		";

	}
	
	echo "
					<tfoot>
							<tr>
							<th colspan='10' align='left'> 
	";
	setupLinks($pageNum, $maxPage, $orderBy, $curOrderType);
	echo "							</th>
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
