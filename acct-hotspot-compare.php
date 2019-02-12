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
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "radacctid";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";
	


	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for hotspot comparison on page: ";

?>

<?php
	include("menu-accounting-hotspot.php");	
?>

<?php
        include_once ("library/tabber/tab-layout.php");
?>


		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','accthotspotcompare.php'); ?>
		<h144>&#x2754;</h144></a></h2>
				
		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','accthotspotcompare') ?>
			<br/>
		</div>
		<br/>


<div class="tabber">

     <div class="tabbertab" title="Account Info">
	 <br/>
	 
<?php

	include 'library/opendb.php';
	include 'include/management/pages_common.php';

	$sql = "SELECT ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
		".name AS hotspot, count(distinct(UserName)) AS uniqueusers, count(radacctid) AS totalhits, ".
		" avg(AcctSessionTime) AS avgsessiontime, sum(AcctSessionTime) AS totaltime, ".
		" avg(AcctInputOctets) AS avgInputOctets, sum(AcctInputOctets) AS sumInputOctets, ".
		" avg(AcctOutputOctets) AS avgOutputOctets, sum(AcctOutputOctets) AS sumOutputOctets ".
		" FROM ".
		$configValues['CONFIG_DB_TBL_RADACCT']." JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
		" on (".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid LIKE ".
		$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac) GROUP BY ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
		".name  ORDER BY $orderBy $orderType;";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";


	
        echo "<table border='0' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>".t('all','Records')."</th>
                                </tr>
                        </thead>
                ";

	if ($orderType == "asc") {
			$orderType = "desc";
	} else  if ($orderType == "desc") {
			$orderType = "asc";
	}
	
	echo "<thread> <tr>
                <th scope='col'> 
			<br/>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=hotspot&orderType=$orderType\">
			".t('all','HotSpot')."</a>
			</th>
			<th scope='col'> 
			<br/>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=uniqueusers&orderType=$orderType\">
			".t('all','UniqueUsers')."</a>
			</th>
			<th scope='col'> 
			<br/>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=totalhits&orderType=$orderType\">
			".t('all','TotalHits')."</a>
			</th>
			<th scope='col'> 
			<br/>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=avgsessiontime&orderType=$orderType\">
			".t('all','AverageTime')."</a>
			</th>
			<th scope='col'> 
			<br/>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=totaltime&orderType=$orderType\">
			".t('all','TotalTime')."</a>
			</th>
			<th scope='col'> 
			<br/>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=sumInputOctets&orderType=$orderType\">
			Total Uploads</a>
			</th>
			<th scope='col'> 
			<br/>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=sumOutputOctets&orderType=$orderType\">
			Total Downloads</a>
			</th>
        </tr> </thread>";
	while($row = $res->fetchRow()) {
                echo "<tr>
                        <td> $row[0] </td>
                        <td> $row[1] </td>
                        <td> $row[2] </td>
                        <td> ".time2str($row[3])." </td>
                        <td> ".time2str($row[4])." </td>
			<td> ".toxbyte($row[6])."</td>
			<td> ".toxbyte($row[8])."</td>
                </tr>";
        }
        echo "</table>";

        include 'library/closedb.php';
?>

	</div>

     <div class="tabbertab" title="Graph">

<?php
	echo "<br/><br/><br/><center>";
        echo "<img src=\"library/graphs-hotspot-compare-unique-users.php\" /><br/><br/>";
        echo "<img src=\"library/graphs-hotspot-compare-hits.php\" /><br/><br/>";
        echo "<img src=\"library/graphs-hotspot-compare-time.php\" /><br/><br/>";
	echo "</center>";
?>

	</div>

</div>


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
