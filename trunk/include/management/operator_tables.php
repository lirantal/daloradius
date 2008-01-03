<?php
/*********************************************************************
* Name: operator_tables.php
* Author: Liran tal <liran.tal@gmail.com>
* 
* Used to provide a listing of the available pages which
* operators may have access to as taken from the operators table in
* the database
*
*********************************************************************/

$arrayPagesAvailable = array(
	 'mng_search' => 'Management Core',
	 'mng_batch' => 'Management Core',
	 'mng_del' => 'Management Core',
	 'mng_edit' => 'Management Core',
	 'mng_new' => 'Management Core',
	 'mng_new_quick' => 'Management Core',
	 'mng_list_all' => 'Management Core',
	 'mng_hs_del' => 'Management HotSpot',
	 'mng_hs_edit' => 'Management HotSpot',
	 'mng_hs_new' => 'Management HotSpot',
	 'mng_hs_list' => 'Management HotSpot',
	 'mng_rad_nas_del' => 'Management NAS',
	 'mng_rad_nas_edit' => 'Management NAS',
	 'mng_rad_nas_new' => 'Management NAS',
	 'mng_rad_nas_list' => 'Management NAS',
	 'mng_rad_usergroup_del' => 'Management UserGroup',
	 'mng_rad_usergroup_edit' => 'Management UserGroup',
	 'mng_rad_usergroup_new' => 'Management UserGroup',
	 'mng_rad_usergroup_list_user' => 'Management UserGroup',
	 'mng_rad_usergroup_list' => 'Management UserGroup',
	 'mng_rad_groupcheck_del' => 'Management Groups',
	 'mng_rad_groupcheck_list' => 'Management Groups',
	 'mng_rad_groupcheck_new' => 'Management Groups',
	 'mng_rad_groupcheck_edit' => 'Management Groups',
	 'mng_rad_groupreply_del' => 'Management Groups',
	 'mng_rad_groupreply_list' => 'Management Groups',
	 'mng_rad_groupreply_new' => 'Management Groups',
	 'mng_rad_groupreply_edit' => 'Management Groups',
	 'mng_rad_profiles_new' => 'Management Profiles',
	 'mng_rad_profiles_edit' => 'Management Profiles',
	 'mng_rad_profiles_del' => 'Management Profiles',
	 'mng_rad_profiles_list' => 'Management Profiles',
	 'rep_topusers' => 'Reporting Core',
	 'rep_online' => 'Reporting Core',
	 'rep_lastconnect' => 'Reporting Core',
	 'rep_logs_radius' => 'Reporting Logs',
	 'rep_stat_radius' => 'Reporting Status',
	 'rep_stat_server' => 'Reporting Status',
	 'rep_logs_system' => 'Reporting Logs',
	 'rep_logs_boot' => 'Reporting Logs',
	 'rep_logs_daloradius' => 'Reporting Logs',
	 'acct_active' => 'Accounting',
	 'acct_username' => 'Accounting',
	 'acct_all' => 'Accounting',
	 'acct_date' => 'Accounting',
	 'acct_ipaddress' => 'Accounting',
	 'acct_nasipaddress' => 'Accounting',
	 'acct_hotspot_accounting' => 'Accounting',
	 'acct_hotspot_compare' => 'Accounting',
	 'acct_custom_query' => 'Accounting',
	 'bill_persecond' => 'Billing',
	 'bill_prepaid' => 'Billing',
	 'bill_rates_del' => 'Billing',
	 'bill_rates_new' => 'Billing',
	 'bill_rates_edit' => 'Billing',
	 'bill_rates_list' => 'Billing',
	 'gis_editmap' => 'GeoLocation',
	 'gis_viewmap' => 'GeoLocation',
	 'graphs_alltime_logins' => 'Graphs',
	 'graphs_alltime_traffic_compare' => 'Graphs',
	 'graphs_overall_download' => 'Graphs',
	 'graphs_overall_upload' => 'Graphs',
	 'graphs_overall_logins' => 'Graphs',
	 'config_db' => 'Configuration Core',
	 'config_interface' => 'Configuration Core',
	 'config_lang' => 'Configuration Core',
	 'config_logging' => 'Configuration Core',
	 'config_maint_test_user' => 'Configuration Maintenance',
	 'config_maint_disconnect_user' => 'Configuration Maintenance',
	 'config_operators_del' => 'Configuration Operators',
	 'config_operators_list' => 'Configuration Operators',
	 'config_operators_edit' => 'Configuration Operators',
	 'config_operators_new' => 'Configuration Operators'
	 );

function drawPagesPermissions($arrayPagesAvailable, $operator_username = "") {

if ($operator_username)			// only if this page was called from the config-operators-edit.php page
	include 'library/opendb.php';   // or some other page which requires looking up the records for a specific operator
					// then we include the required file for database operations


echo "<br/><br/>
<table border='2' class='table1' width='600'>
<thead>
                <tr>
                <th colspan='10'>Permission to access pages</th>
                </tr>
</thead>
";


/* 
 * Home Pages category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryHome')">
    <b> Home Pages </b> <br/>
    <div id="categoryHome" style="display:none;visibility:visible" >
EOF;
echo "<br/><table border='2' class='table1'>";
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Home Pages')
			continue;

		echo "<tr><td width='350'>";
		echo "<font size='2'> $page </font>";
		echo "</td><td>";

		if ($operator_username) {
			$sql = "SELECT $page FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username='$operator_username'";
			$res = $dbSocket->query($sql);
			$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
			$pageTest = $row[$page];
			if ( (strcasecmp($pageTest, "y") == 0) || (strcasecmp($pageTest, "yes") == 0) || (strcasecmp($pageTest, "on") == 0) ) {
			echo "<select name='$page'>
			      <option value='yes' selected> Enabled
			      <option value='no'> Disabled
			      </select>
				<br/>
			";
			} else {
			echo "<select name='$page'>
			      <option value='yes'> Enabled
			      <option value='no' selected> Disabled
			      </select>
				<br/>
			";
			}
		} else  {
			echo "<select name='$page'>
			      <option value='yes'> Enabled
			      <option value='no'> Disabled
			      </select>
				<br/>
			";
		}
echo "</td></tr>";		
				
	}
echo "</table>";
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/



/* 
 * Management Core category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryManagementCore')">
    <b> Management - Core </b> <br/>
    <div id="categoryManagementCore" style="display:none;visibility:visible" >
EOF;
echo "<br/><table border='2' class='table1'>";
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Management Core')
			continue;

                echo "<tr><td width='350'>";
		echo "<font size='2'> $page </font>";
                echo "</td><td>";

                if ($operator_username) {
                        $sql = "SELECT $page FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username='$operator_username'";
                        $res = $dbSocket->query($sql);
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                        $pageTest = $row[$page];
                        if ( (strcasecmp($pageTest, "y") == 0) || (strcasecmp($pageTest, "yes") == 0) || (strcasecmp($pageTest, "on") == 0) ) {
                        echo "<select name='$page'>
                              <option value='yes' selected> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                        } else {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no' selected> Disabled
                              </select>
                                <br/>
                        ";
			}
                } else  {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                }
echo "</td></tr>";

	}
echo "</table>";
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/




/* 
 * Management HotSpot category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryManagementHotSpot')">
    <b> Management - HotSpot </b> <br/>
    <div id="categoryManagementHotSpot" style="display:none;visibility:visible" >
EOF;
echo "<br/><table border='2' class='table1'>";
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Management HotSpot')
			continue;

                echo "<tr><td width='350'>";
                echo "<font size='2'> $page </font>";
                echo "</td><td>";

                if ($operator_username) {
                        $sql = "SELECT $page FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username='$operator_username'";
                        $res = $dbSocket->query($sql);
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                        $pageTest = $row[$page];
                        if ( (strcasecmp($pageTest, "y") == 0) || (strcasecmp($pageTest, "yes") == 0) || (strcasecmp($pageTest, "on") == 0) ) {
                        echo "<select name='$page'>
                              <option value='yes' selected> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                        } else {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no' selected> Disabled
                              </select>
                                <br/>
                        ";
			}
                } else  {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                }
echo "</td></tr>";
	}
echo "</table>";
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/



/* 
 * Management NAS category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryManagementNAS')">
    <b> Management - NAS </b> <br/>
    <div id="categoryManagementNAS" style="display:none;visibility:visible" >
EOF;
echo "<br/><table border='2' class='table1'>";
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Management NAS')
			continue;

                echo "<tr><td width='350'>";
		echo "<font size='2'> $page </font>";
                echo "</td><td>";

                if ($operator_username) {
                        $sql = "SELECT $page FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username='$operator_username'";
                        $res = $dbSocket->query($sql);
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                        $pageTest = $row[$page];
                        if ( (strcasecmp($pageTest, "y") == 0) || (strcasecmp($pageTest, "yes") == 0) || (strcasecmp($pageTest, "on") == 0) ){
                        echo "<select name='$page'>
                              <option value='yes' selected> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
			} else {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no' selected> Disabled
                              </select>
                                <br/>
                        ";
			}
                } else  {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                }
echo "</td></tr>";
	}
echo "</table>";
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/




/* 
 * Management UserGroup category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryManagementUserGroup')">
    <b> Management - UserGroup </b> <br/>
    <div id="categoryManagementUserGroup" style="display:none;visibility:visible" >
EOF;
echo "<br/><table border='2' class='table1'>";
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Management UserGroup')
			continue;

                echo "<tr><td width='350'>";
                echo "<font size='2'> $page </font>";
                echo "</td><td>";

                if ($operator_username) {
                        $sql = "SELECT $page FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username='$operator_username'";
                        $res = $dbSocket->query($sql);
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                        $pageTest = $row[$page];
                        if ( (strcasecmp($pageTest, "y") == 0) || (strcasecmp($pageTest, "yes") == 0) || (strcasecmp($pageTest, "on") == 0) ) {
                        echo "<select name='$page'>
                              <option value='yes' selected> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";

                        } else {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no' selected> Disabled
                              </select>
                                <br/>
                        ";

			}
                } else  {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";

                }
echo "</td></tr>";
	}
echo "</table>";
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/




/* 
 * Management Groups category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryManagementGroups')">
    <b> Management - Groups </b> <br/>
    <div id="categoryManagementGroups" style="display:none;visibility:visible" >
EOF;
echo "<br/><table border='2' class='table1'>";
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Management Groups')
			continue;

                echo "<tr><td width='350'>";
                echo "<font size='2'> $page </font>";
                echo "</td><td>";

                if ($operator_username) {
                        $sql = "SELECT $page FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username='$operator_username'";
                        $res = $dbSocket->query($sql);
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                        $pageTest = $row[$page];
                        if ( (strcasecmp($pageTest, "y") == 0) || (strcasecmp($pageTest, "yes") == 0) || (strcasecmp($pageTest, "on") == 0) ) {
                        echo "<select name='$page'>
                              <option value='yes' selected> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                        } else {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no' selected> Disabled
                              </select>
                                <br/>
                        ";
			}
                } else  {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                }
echo "</td></tr>";

	}
echo "</table>";
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/



/* 
 * Management Profiles category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryManagementProfiles')">
    <b> Management - Profiles </b> <br/>
    <div id="categoryManagementProfiles" style="display:none;visibility:visible" >
EOF;
echo "<br/><table border='2' class='table1'>";
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Management Profiles')
			continue;

                echo "<tr><td width='350'>";
                echo "<font size='2'> $page </font>";
                echo "</td><td>";

                if ($operator_username) {
                        $sql = "SELECT $page FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username='$operator_username'";
                        $res = $dbSocket->query($sql);
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                        $pageTest = $row[$page];
                        if ( (strcasecmp($pageTest, "y") == 0) || (strcasecmp($pageTest, "yes") == 0) || (strcasecmp($pageTest, "on") == 0) ) {
                        echo "<select name='$page'>
                              <option value='yes' selected> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                        } else {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no' selected> Disabled
                              </select>
                                <br/>
                        ";
			}
                } else  {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                }
echo "</td></tr>";
	}
echo "</table>";
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/





/* 
 * Reporting Core category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryReportingCore')">
    <b> Reporting Core </b> <br/>
    <div id="categoryReportingCore" style="display:none;visibility:visible" >
EOF;
echo "<br/><table border='2' class='table1'>";
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Reporting Core')
			continue;

                echo "<tr><td width='350'>";
                echo "<font size='2'> $page </font>";
                echo "</td><td>";

                if ($operator_username) {
                        $sql = "SELECT $page FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username='$operator_username'";
                        $res = $dbSocket->query($sql);
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                        $pageTest = $row[$page];
                        if ( (strcasecmp($pageTest, "y") == 0) || (strcasecmp($pageTest, "yes") == 0) || (strcasecmp($pageTest, "on") == 0) ) {
                        echo "<select name='$page'>
                              <option value='yes' selected> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                        } else {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no' selected> Disabled
                              </select>
                                <br/>
                        ";
			}
                } else  {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                }
echo "</td></tr>";
	}
echo "</table>";
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/



/* 
 * Reporting Logs category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryReportingLogs')">
    <b> Reporting Logs </b> <br/>
    <div id="categoryReportingLogs" style="display:none;visibility:visible" >
EOF;
echo "<br/><table border='2' class='table1'>";
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Reporting Logs')
			continue;

                echo "<tr><td width='350'>";
                echo "<font size='2'> $page </font>";
                echo "</td><td>";

                if ($operator_username) {
                        $sql = "SELECT $page FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username='$operator_username'";
                        $res = $dbSocket->query($sql);
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                        $pageTest = $row[$page];
                        if ( (strcasecmp($pageTest, "y") == 0) || (strcasecmp($pageTest, "yes") == 0) || (strcasecmp($pageTest, "on") == 0) ) {
                        echo "<select name='$page'>
                              <option value='yes' selected> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                        } else {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no' selected> Disabled
                              </select>
                                <br/>
                        ";
			}
                } else  {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                }
echo "</td></tr>";
	}
echo "</table>";
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/



/* 
 * Reporting Status category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryReportingStatus')">
    <b> Reporting Status </b> <br/>
    <div id="categoryReportingStatus" style="display:none;visibility:visible" >
EOF;
echo "<br/><table border='2' class='table1'>";
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Reporting Status')
			continue;

                echo "<tr><td width='350'>";
                echo "<font size='2'> $page </font>";
                echo "</td><td>";

                if ($operator_username) {
                        $sql = "SELECT $page FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username='$operator_username'";
                        $res = $dbSocket->query($sql);
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                        $pageTest = $row[$page];
                        if ( (strcasecmp($pageTest, "y") == 0) || (strcasecmp($pageTest, "yes") == 0) || (strcasecmp($pageTest, "on") == 0) ) {
                        echo "<select name='$page'>
                              <option value='yes' selected> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                        } else {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no' selected> Disabled
                              </select>
                                <br/>
                        ";
			}
                } else  {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                }
echo "</td></tr>";
	}
echo "</table>";
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/



/* 
 * Accounting category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryAccounting')">
    <b> Accounting </b> <br/>
    <div id="categoryAccounting" style="display:none;visibility:visible" >
EOF;
echo "<br/><table border='2' class='table1'>";
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Accounting')
			continue;

                echo "<tr><td width='350'>";
                echo "<font size='2'> $page </font>";
                echo "</td><td>";

                if ($operator_username) {
                        $sql = "SELECT $page FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username='$operator_username'";
                        $res = $dbSocket->query($sql);
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                        $pageTest = $row[$page];
                        if ( (strcasecmp($pageTest, "y") == 0) || (strcasecmp($pageTest, "yes") == 0) || (strcasecmp($pageTest, "on") == 0) ) {
                        echo "<select name='$page'>
                              <option value='yes' selected> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                        } else {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no' selected> Disabled
                              </select>
                                <br/>
                        ";
			}
                } else  {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                }
echo "</td></tr>";
	}
echo "</table>";
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/






/* 
 * Billing category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryBilling')">
    <b> Billing </b> <br/>
    <div id="categoryBilling" style="display:none;visibility:visible" >
EOF;
echo "<br/><table border='2' class='table1'>";
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Billing')
			continue;

                echo "<tr><td width='350'>";
                echo "<font size='2'> $page </font>";
                echo "</td><td>";

                if ($operator_username) {
                        $sql = "SELECT $page FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username='$operator_username'";
                        $res = $dbSocket->query($sql);
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                        $pageTest = $row[$page];
                        if ( (strcasecmp($pageTest, "y") == 0) || (strcasecmp($pageTest, "yes") == 0) || (strcasecmp($pageTest, "on") == 0) ) {
                        echo "<select name='$page'>
                              <option value='yes' selected> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                        } else {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no' selected> Disabled
                              </select>
                                <br/>
                        ";
			}
                } else  {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                }
echo "</td></tr>";
	}
echo "</table>";
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/





/* 
 * GeoLocation category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryGeoLocation')">
    <b> GeoLocation </b> <br/>
    <div id="categoryGeoLocation" style="display:none;visibility:visible" >
EOF;
echo "<br/><table border='2' class='table1'>";
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'GeoLocation')
			continue;

                echo "<tr><td width='350'>";
                echo "<font size='2'> $page </font>";
                echo "</td><td>";

                if ($operator_username) {
                        $sql = "SELECT $page FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username='$operator_username'";
                        $res = $dbSocket->query($sql);
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                        $pageTest = $row[$page];
                        if ( (strcasecmp($pageTest, "y") == 0) || (strcasecmp($pageTest, "yes") == 0) || (strcasecmp($pageTest, "on") == 0) ) {
                        echo "<select name='$page'>
                              <option value='yes' selected> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                        } else {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no' selected> Disabled
                              </select>
                                <br/>
                        ";
			}
                } else  {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                }
echo "</td></tr>";
	}
echo "</table>";
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/



/* 
 * Graphs category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryGraphs')">
    <b> Graphs </b> <br/>
    <div id="categoryGraphs" style="display:none;visibility:visible" >
EOF;
echo "<br/><table border='2' class='table1'>";
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Graphs')
			continue;

                echo "<tr><td width='350'>";
                echo "<font size='2'> $page </font>";
                echo "</td><td>";

                if ($operator_username) {
                        $sql = "SELECT $page FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username='$operator_username'";
                        $res = $dbSocket->query($sql);
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                        $pageTest = $row[$page];
                        if ( (strcasecmp($pageTest, "y") == 0) || (strcasecmp($pageTest, "yes") == 0) || (strcasecmp($pageTest, "on") == 0) ) {
                        echo "<select name='$page'>
                              <option value='yes' selected> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                        } else {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no' selected> Disabled
                              </select>
                                <br/>
                        ";
			}
                } else  {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                }
echo "</td></tr>";
	}
echo "</table>";
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/



/* 
 * Configuration category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryConfigurationCore')">
    <b> Configuration - Core</b> <br/>
    <div id="categoryConfigurationCore" style="display:none;visibility:visible" >
EOF;
echo "<br/><table border='2' class='table1'>";
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Configuration Core')
			continue;

                echo "<tr><td width='350'>";
                echo "<font size='2'> $page </font>";
                echo "</td><td>";

                if ($operator_username) {
                        $sql = "SELECT $page FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username='$operator_username'";
                        $res = $dbSocket->query($sql);
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                        $pageTest = $row[$page];
                        if ( (strcasecmp($pageTest, "y") == 0) || (strcasecmp($pageTest, "yes") == 0) || (strcasecmp($pageTest, "on") == 0) ) {
                        echo "<select name='$page'>
                              <option value='yes' selected> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                        } else {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no' selected> Disabled
                              </select>
                                <br/>
                        ";
			}
                } else  {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                }
echo "</td></tr>";
	}
echo "</table>";
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/


/* 
 * Configuration Maintenance category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>	
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryConfigurationMaint')">
    <b> Configuration - Maintenance </b> <br/>
    <div id="categoryConfigurationMaint" style="display:none;visibility:visible" >
EOF;
echo "<br/><table border='2' class='table1'>";
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Configuration Maintenance')
			continue;

                echo "<tr><td width='350'>";
                echo "<font size='2'> $page </font>";
                echo "</td><td>";

                if ($operator_username) {
                        $sql = "SELECT $page FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username='$operator_username'";
                        $res = $dbSocket->query($sql);
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                        $pageTest = $row[$page];
                        if ( (strcasecmp($pageTest, "y") == 0) || (strcasecmp($pageTest, "yes") == 0) || (strcasecmp($pageTest, "on") == 0) ) {
                        echo "<select name='$page'>
                              <option value='yes' selected> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                        } else {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no' selected> Disabled
                              </select>
                                <br/>
                        ";
			}
                } else  {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                }
echo "</td></tr>";
	}
echo "</table>";
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/




/*
 *  block ends ***********************************************************************
*/


/* 
 * Configuration Operators category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>	
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryConfigurationOperators')">
    <b> Configuration - Operators </b> <br/>
    <div id="categoryConfigurationOperators" style="display:none;visibility:visible" >
EOF;
echo "<br/><table border='2' class='table1'>";
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Configuration Operators')
			continue;

                echo "<tr><td width='350'>";
                echo "<font size='2'> $page </font>";
                echo "</td><td>";

                if ($operator_username) {
                        $sql = "SELECT $page FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username='$operator_username'";
                        $res = $dbSocket->query($sql);
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                        $pageTest = $row[$page];
                        if ( (strcasecmp($pageTest, "y") == 0) || (strcasecmp($pageTest, "yes") == 0) || (strcasecmp($pageTest, "on") == 0) ) {
                        echo "<select name='$page'>
                              <option value='yes' selected> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                        } else {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no' selected> Disabled
                              </select>
                                <br/>
                        ";
			}
                } else  {
                        echo "<select name='$page'>
                              <option value='yes'> Enabled
                              <option value='no'> Disabled
                              </select>
                                <br/>
                        ";
                }
echo "</td></tr>";
	}
echo "</table>";
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/



echo "</table>";


if ($operator_username)			// same for including opendb.php file, we only require the closedb if the function
	include 'library/closedb.php';  // was called with a specific operator which requires database look ups
	
}


?>

