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
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "time";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "desc";


	include_once('library/config_read.php');
	$log = "visited page: ";
	$logQuery = "performed query on page: ";
	$logDebugSQL = "";
	
	$softDelay = $configValues['CONFIG_DASHBOARD_DALO_DELAYSOFT'];
	$hardDelay = $configValues['CONFIG_DASHBOARD_DALO_DELAYHARD'];

?>

<?php

    include ("menu-reports-hb.php");
  	
?>	


	<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"  onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','rephbdashboard.php'); ?>
		<h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','rephbdashboard') ?>
			<br/>
		</div>


<?php

	include 'include/management/pages_common.php';
	include 'library/opendb.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file


	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page
	$sql = "SELECT ".
			$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name as hotspotname,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wan_iface,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wan_ip,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wan_mac,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wan_gateway,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wifi_iface,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wifi_ip,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wifi_mac,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wifi_ssid,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wifi_key,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wifi_channel,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".lan_iface,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".lan_mac,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".lan_ip,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".uptime,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".memfree,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".cpu,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wan_bup,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wan_bdown,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".firmware,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".firmware_revision,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".mac,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".time ".
			" FROM ".$configValues['CONFIG_DB_TBL_DALONODE'].
			" LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
			" ON ".
			"(".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac = ".
			$configValues['CONFIG_DB_TBL_DALONODE'].".mac) ";
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();
	$logDebugSQL .= $sql . "\n";


	$sql = "SELECT ".
			$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name as hotspotname,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wan_iface,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wan_ip,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wan_mac,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wan_gateway,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wifi_iface,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wifi_ip,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wifi_mac,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wifi_ssid,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wifi_key,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wifi_channel,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".lan_iface,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".lan_mac,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".lan_ip,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".uptime,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".memfree,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wan_bup,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".wan_bdown,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".firmware,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".firmware_revision,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".mac,".
			$configValues['CONFIG_DB_TBL_DALONODE'].".time, ".
			$configValues['CONFIG_DB_TBL_DALONODE'].".cpu ".
			" FROM ".$configValues['CONFIG_DB_TBL_DALONODE'].
			" LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
			" ON ".
			"(".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac = ".
			$configValues['CONFIG_DB_TBL_DALONODE'].".mac) ".
			//" ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
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
							<th colspan='11' align='left'> 

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
		".t('all','HotSpot')."</a>
		</th>

		<th scope='col'> 
		".t('all','Firmware')."
		</th>
		
		<th scope='col'> 
		".t('all','WanIface')."
		</th>

		<th scope='col'> 
		".t('all','LanIface')."
		</th>

		<th scope='col'> 
		".t('all','WifiIface')."
		</th>

		<th scope='col'> 
		".t('all','Uptime')."
		</th>

		<th scope='col'> 
		".t('all','CPU')."
		</th>
		
		<th scope='col'> 
		".t('all','Memfree')."
		</th>

		<th scope='col'> 
		".t('all','BandwidthUp')."
		</th>

		<th scope='col'> 
		".t('all','BandwidthDown')."
		</th>

		<th scope='col'> 
		".t('all','CheckinTime')."
		</th>

		</tr> </thread>";

	while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {

		
		
		//$js = "javascript:ajaxGeneric('include/management/retUserInfo.php','retBandwidthInfo','divContainerUserInfo','username=".$row[0]."');";
		$content =  '<a class="toolTip" href="mng-hs-edit.php?name='.$row['hotspotname'].'">'.t('Tooltip','HotspotEdit').'</a>';
		$content .= '<br/><br/><b>'.t('all','NASMAC').':</b> '.$row['mac'];
		$str = addToolTipBalloon(array(
									'content' => $content,
									'onClick' => '',
									'value' => '<b>'.$row['hotspotname'].'</b>',
									'divId' => '',
		
							));

		echo "
			<tr>
				<td>
					$str
				</td>
			
				<td>".
					$row['firmware']."
					<br/>".
					$row['firmware_revision']."
				</td>
			";
					
					
		$content = '<b>'.t('all','WanIface').":</b> ".$row['wan_iface'].
					"<br/>".
					'<b>'.t('all','WanMAC').":</b> ".$row['wan_mac'].
					"<br/>".
					'<b>'.t('all','WanIP').":</b> ".$row['wan_ip'].
					"<br/>".
					'<b>'.t('all','WanGateway').":</b> ".$row['wan_ip'];
		$value = '<b>'.t('all','WanIP').":</b> ".$row['wan_ip'];
		$str = addToolTipBalloon(array(
									'content' => $content,
									'onClick' => '',
									'value' => $value,
									'divId' => '',
		
							));
							
		echo "<td> $str </td>";
				
		
		
		$content = t('all','LanIface').":</b> ".$row['lan_iface'].
						"<br/><b>".
					t('all','LanMAC').":</b> ".$row['lan_mac'].
						"<br/><b>".
					t('all','LanIP').":</b> ".$row['lan_ip'];
		$value = '<b>'.t('all','LanIP').":</b> ".$row['lan_ip'];
		$str = addToolTipBalloon(array(
									'content' => $content,
									'onClick' => '',
									'value' => $value,
									'divId' => '',
		
							));
							
		echo "<td> $str </td>";
				
		

		$content = t('all','WifiIface').":</b> ".$row['wifi_iface'].
						"<br/><b>".
					t('all','WifiMAC').":</b> ".$row['wifi_mac'].
						"<br/><b>".
					t('all','WifiIP').":</b> ".$row['wifi_ip'].
						"<br/><b>".
					t('all','WifiSSID').":</b> ".$row['wifi_ssid'].
						"<br/><b>".
					t('all','WifiKey').":</b> ".$row['wifi_key'].
						"<br/><b>".
					t('all','WifiChannel').":</b> ".$row['wifi_channel'];
		$value = '<b>'.t('all','WifiSSID').":</b> ".$row['wifi_ssid'].
					"<br/><b>".
					t('all','WifiKey').":</b> ".$row['wifi_key'];
		$str = addToolTipBalloon(array(
									'content' => $content,
									'onClick' => '',
									'value' => $value,
									'divId' => '',
		
							));
							
		echo "<td> $str </td>";
	
		
		// calculate time delay
		$currTime = time(); 
		$checkinTime = strtotime($row['time']);
		if ($currTime - $checkinTime >= (60*$hardDelay)) {

			// this is hard delay
			$delayColor = 'red';
			
		} elseif ( 
			($currTime - $checkinTime >= (60*$softDelay))
			&& ($currTime - $checkinTime < (60*$hardDelay))
			)
		{

			// this is soft delay
			$delayColor = 'orange';
			
		} else {
			
			// this is no delay at all, meaning not above 5 minutes delay
			$delayColor = 'green';
		}
			
		echo "
				<td>".
					time2str($row['uptime'])."
				</td>

				<td>".
					$row['cpu']."
				</td>
				
				<td>".
					$row['memfree']."
				</td>

				<td>".
					toxbyte($row['wan_bup'])."
				</td>

				<td>".
					toxbyte($row['wan_bdown'])."
				</td>

				<td> <font color='$delayColor'> ".
					$row['time']."
					</font>
				</td>
				
			</tr>
		";

	}
	
	echo "
					<tfoot>
							<tr>
							<th colspan='11' align='left'> 
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
