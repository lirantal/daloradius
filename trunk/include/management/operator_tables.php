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
	 'index_last_connect' => 'Home Pages',
	 'index_radius_log' => 'Home Pages',
	 'index_radius_stat' => 'Home Pages',
	 'index_server_stat' => 'Home Pages',
	 'index_system_log' => 'Home Pages',
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
	 'mng_rad_groupcheck_del' => 'Management GroupCheck',
	 'mng_rad_groupcheck_list' => 'Management GroupCheck',
	 'mng_rad_groupcheck_new' => 'Management GroupCheck',
	 'mng_rad_groupcheck_edit' => 'Management GroupCheck',
	 'mng_rad_groupreply_del' => 'Management GroupReply',
	 'mng_rad_groupreply_list' => 'Management GroupReply',
	 'mng_rad_groupreply_new' => 'Management GroupReply',
	 'mng_rad_groupreply_edit' => 'Management GroupReply',
	 'rep_topusers' => 'Reporting',
	 'rep_username' => 'Reporting',
	 'acct_active' => 'Accounting',
	 'acct_username' => 'Accounting',
	 'acct_all' => 'Accounting',
	 'acct_date' => 'Accounting',
	 'acct_ipaddress' => 'Accounting',
	 'acct_nasipaddress' => 'Accounting',
	 'acct_hotspot' => 'Accounting',
	 'acct_hotspot_compare' => 'Accounting',
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
	 'config_db' => 'Configuration',
	 'config_interface' => 'Configuration',
	 'config_lang' => 'Configuration',
	 'config_logging' => 'Configuration',
	 'config_maint_test_user' => 'Configuration Maintenance',
	 );

function drawPagesPermissions($arrayPagesAvailable) {


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
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Home Pages')
			continue;
		echo " <input type=checkbox name='$page'>$page <br/>";
	}
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
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Management Core')
			continue;
		echo " <input type=checkbox name='$page'>$page <br/>";
	}
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
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Management HotSpot')
			continue;
		echo " <input type=checkbox name='$page'>$page <br/>";
	}
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
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Management NAS')
			continue;
		echo " <input type=checkbox name='$page'>$page <br/>";
	}
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
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Management UserGroup')
			continue;
		echo " <input type=checkbox name='$page'>$page <br/>";
	}
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/




/* 
 * Management GroupCheck category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryManagementGroupCheck')">
    <b> Management - GroupCheck </b> <br/>
    <div id="categoryManagementGroupCheck" style="display:none;visibility:visible" >
EOF;
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Management GroupCheck')
			continue;
		echo " <input type=checkbox name='$page'>$page <br/>";
	}
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/



/* 
 * Management GroupReply category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryManagementGroupReply')">
    <b> Management - GroupReply </b> <br/>
    <div id="categoryManagementGroupReply" style="display:none;visibility:visible" >
EOF;
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Management GroupReply')
			continue;
		echo " <input type=checkbox name='$page'>$page <br/>";
	}
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/





/* 
 * Reporting category related pages *************************************************
 *
*/

echo <<<EOF
	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryReporting')">
    <b> Reporting </b> <br/>
    <div id="categoryReporting" style="display:none;visibility:visible" >
EOF;
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Reporting')
			continue;
		echo " <input type=checkbox name='$page'>$page <br/>";
	}
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
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Accounting')
			continue;
		echo " <input type=checkbox name='$page'>$page <br/>";
	}
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
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Billing')
			continue;
		echo " <input type=checkbox name='$page'>$page <br/>";
	}
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
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'GeoLocation')
			continue;
		echo " <input type=checkbox name='$page'>$page <br/>";
	}
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
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Graphs')
			continue;
		echo " <input type=checkbox name='$page'>$page <br/>";
	}
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
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryConfiguration')">
    <b> Configuration </b> <br/>
    <div id="categoryConfiguration" style="display:none;visibility:visible" >
EOF;
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Configuration')
			continue;
		echo " <input type=checkbox name='$page'>$page <br/>";
	}
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
	foreach ($arrayPagesAvailable as $page => $descr) {
		if ($descr != 'Configuration Maintenance')
			continue;
		echo " <input type=checkbox name='$page'>$page <br/>";
	}
	echo "</td></tr>
		</div>";

/*
 *  block ends ***********************************************************************
*/




echo "</table>";




	
}


?>

