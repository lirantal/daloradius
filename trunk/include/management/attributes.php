<?php
/*********************************************************************
* Name: attributes.php
* Author: Liran tal <liran.tal@gmail.com>
* 
* This file is used by the management page (edit user) 
* and it's general purpose is to return the table string
* for a given attribute name
*
*********************************************************************/

function drawAttributes() {

	$arraySessionAttr = array(
	 'Expiration' => 'date',
	 'Session-Timeout' => 'seconds',
	 'Idle-Timeout' => 'seconds',
	 'Max-All-Session' => 'seconds',
	 'Framed-IP-Address' => 'none',
	 'Framed-IP-Netmask' => 'none',
	 'Framed-Pool' => 'none',
	 'CHAP-Password' => 'none',
	 'CHAP-Challenge' => 'none',
	 'Service-Type' => 'servicetype',
	 'Reply-Message' => 'none'
	 );

	 
	$arrayNasAttr = array(
	 'Calling-Station-Id' => 'none',
	 'Called-Station-Id' => 'none',
	 'NAS-ID' => 'none',
	 'NAS-IP-Address' => 'none',
	 'NAS-Port-Type' => 'none'
	 );	 
	 
	 
	$arrayWISPrAttr = array(
	 'WISPr-Location-ID' => 'none',
	 'WISPr-Location-Name' => 'none',
	 'WISPr-Logoff-URL' => 'none',
	 'WISPr-Redirection-URL' => 'none',
	 'WISPr-Bandwidth-Max-Up' => 'speed',
	 'WISPr-Bandwidth-Max-Down' => 'speed',
	 'WISPr-Session-Terminate-Time' => 'date'
	 );	 

	$arrayChillispotAttr = array(
	 'ChilliSpot-Max-Input-Octets' => 'none',
	 'ChilliSpot-Max-Output-Octets' => 'none',
	 'ChilliSpot-Max-Total-Octets' => 'none',
	 'ChilliSpot-UAM-Allowed' => 'none',
	 'ChilliSpot-MAC-Allowed' => 'none',
	 'ChilliSpot-MAC-Interval' => 'none'
	 );	 



	$arrayMikrotikAttr = array(
	 'MikroTik-Rate-Limit' => 'none',
	 );	 

	$arrayAscendAttr = array(
	 'Ascend-Data-Rate' => 'none',
	 'Ascend-Xmit-Rate' => 'none',
	 );	 

	 
echo "<table border='2' class='table1'>";
echo <<<EOF
                                        <thead>
                                                        <tr>
                                                        <th colspan='2'> Attributes </th>
                                                        </tr>
                                        </thead>

	<tr><td>		
    <input type="checkbox" onclick="javascript:toggleShowDiv('categorySession')">
    <b> Session Attributes </b> <br/>
    <div id="categorySession" style="display:none;visibility:visible" >
EOF;
	 $cnt = 0;
	foreach ( $arraySessionAttr as $attrib => $help ) {
		drawAttributesHtml($attrib);
		if ($help == "seconds") 
			drawSelectSeconds($attrib, $cnt);
		if ($help == "speed") 
			drawSelectSpeed($attrib, $cnt);
		if ($help == "date") 
			drawDateHtml($attrib);			
		if ($help == "servicetype") 
			drawSelectServiceType($attrib, $cnt);			
		echo "
		<br/><br/></font>
		</div>
		";
		$cnt++;
	}
	echo "</td></tr>
		</div>";
	
	
echo <<<EOF
	<tr><td>	
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryNas')">
    <b> NAS Attributes </b> <br/>
    <div id="categoryNas" style="display:none;visibility:visible" >
EOF;
	
	$cnt = 0;
	foreach ( $arrayNasAttr as $attrib => $help ) {
		drawAttributesHtml($attrib);
		if ($help == "seconds") 
			drawSelectSeconds($attrib, $cnt);
		if ($help == "speed") 
			drawSelectSpeed($attrib, $cnt);
		if ($help == "date") 
			drawDateHtml($attrib);			
		echo "
		<br/><br/></font>
		</div>
		";
		$cnt++;
	}	
	echo "</td></tr>
		</div>";
	

echo <<<EOF
	<tr><td>
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryWISPr')">
    <b> WISPr Attributes </b> <br/>
    <div id="categoryWISPr" style="display:none;visibility:visible" >
EOF;
	
	$cnt = 0;
	foreach ( $arrayWISPrAttr as $attrib => $help ) {
		drawAttributesHtml($attrib);
		if ($help == "seconds") 
			drawSelectSeconds($attrib, $cnt);
		if ($help == "speed") 
			drawSelectSpeed($attrib, $cnt);
		if ($help == "date") 
			drawDateHtml($attrib);			
		echo "
		<br/><br/></font>
		</div>
		";
		$cnt++;
	}		
	echo "</td></tr>
		</div>";	



echo <<<EOF
	<tr><td>	
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryChillispot')">
    <b> Chillispot Attributes </b> <br/>
    <div id="categoryChillispot" style="display:none;visibility:visible" >
EOF;
	
	$cnt = 0;
	foreach ( $arrayChillispotAttr as $attrib => $help ) {
		drawAttributesHtml($attrib);
		if ($help == "seconds") 
			drawSelectSeconds($attrib, $cnt);
		if ($help == "speed") 
			drawSelectSpeed($attrib, $cnt);
		if ($help == "date") 
			drawDateHtml($attrib);			
		echo "
		<br/><br/></font>
		</div>
		";
		$cnt++;
	}	
	echo "</td></tr>
		</div>";


echo <<<EOF
	<tr><td>
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryAscend')">
    <b> Ascend Attributes </b> <br/>
    <div id="categoryAscend" style="display:none;visibility:visible" >
EOF;
	
	$cnt = 0;
	foreach ( $arrayAscendAttr as $attrib => $help ) {
		drawAttributesHtml($attrib);
		if ($help == "seconds") 
			drawSelectSeconds($attrib, $cnt);
		if ($help == "speed") 
			drawSelectSpeed($attrib, $cnt);
		if ($help == "date") 
			drawDateHtml($attrib);			
		echo "
		<br/><br/></font>
		</div>
		";
		$cnt++;
	}		
	echo "</td></tr>
		</div>";	




echo <<<EOF
	<tr><td>
    <input type="checkbox" onclick="javascript:toggleShowDiv('categoryMikrotik')">
    <b> Mikrotik Attributes </b> <br/>
    <div id="categoryMikrotik" style="display:none;visibility:visible" >
EOF;
	
	$cnt = 0;
	foreach ( $arrayMikrotikAttr as $attrib => $help ) {
		drawAttributesHtml($attrib);
		if ($help == "seconds") 
			drawSelectSeconds($attrib, $cnt);
		if ($help == "speed") 
			drawSelectSpeed($attrib, $cnt);
		if ($help == "date") 
			drawDateHtml($attrib);			
		echo "
		<br/><br/></font>
		</div>
		";
		$cnt++;
	}		
	echo "</td></tr>
		</div>";	






echo "</table>";

	
}


function drawAttributesHtml($attrib) {

        include_once ('op_select_options.php');
        echo <<<EOA
                <font color='#FF0000'>
                <input type="checkbox" onclick="javascript:toggleShowDiv('attributes$attrib')">
                <b>$attrib</b><br/>
                <div id="attributes$attrib" style="display:none;visibility:visible" >
EOA;
        echo "<input value='' id='$attrib' name='$attrib" . "[]'" . ">";
        echo "
                <select name=\"".$attrib."[]\">";
        drawOptions();
        echo "</select>";
        echo "";


}


function drawSelectSpeed($attribute, $counter) {

	echo <<<EOS
		<select onChange="javascript:setText(this.id,'$attribute')" id="option$attribute">
		<option value="1">calculate speed</option>
		<option value="1">bits</option>
		<option value="1024">kilobits</option>
		<option value="1048576">megabits</option>
		</select>
EOS;

}


function drawDateHtml($attribute) {

	echo <<<EOS
		<img src="library/js_date/calendar.gif" onclick="showChooser(this, '$attribute', 'chooserSpan', 1950, 2010, 'd M Y', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
EOS;

}


function drawSelectSeconds($attribute, $counter) {

	echo <<<EOS
		<select onChange="javascript:setText(this.id,'$attribute')" id="option$attribute">
		<option value="1">calculate time</option>
		<option value="1">seconds</option>
		<option value="60">minutes</option>
		<option value="3600">hours</option>
		<option value="86400">days</option>
		<option value="604800">weeks</option>
		<option value="2592000">months (30 days)</option>
		</select>
EOS;

}


function drawSelectServiceType($attribute, $counter) {

	echo <<<EOS
		<select onChange="javascript:setStringText(this.id,'$attribute')" id="option$attribute">
		<option value="">Service-Type...</option>
		<option value="Login-User">Login-User</option>
		<option value="Framed-User">Framed-User</option>
		<option value="Callback-Login-User">Callback-Login-User</option>
		<option value="Callback-Framed-User">Callback-Framed-User</option>
		<option value="Outbound-User">Outbound-User</option>
		<option value="Administrative-User">Administrative-User</option>
		<option value="NAS-Prompt-User">NAS-Prompt-User</option>
		<option value="Authenticate-Only">Authenticate-Only</option>
		<option value="Callback-NAS-Prompt">Callback-NAS-Prompt</option>
		<option value="Call-Check">Call-Check</option>
		<option value="Callback-Administrative">Callback-Administrative</option>
		<option value="Authorize-Only">Authorize-Only</option>
		</select>
EOS;

}



function checkTables($attribute) {
/*
* @param $attribute	The attribute name, Session-Timeout for example
* @return $table		The table name, either radcheck or radreply
*/
    include ('library/config_read.php');

	/* by default we set $table to return the radcheck table */
	$table = $configValues['CONFIG_DB_TBL_RADCHECK'];
	
	/* then we check to see if the given attribute should belong
	   to the radreply table, if so, we set $table to radreply
	   otherwise we end the switch case without doing anything,
	   and $table remains in it's default state of being radcheck
	*/

	switch ($attribute) {
		case "Reply-Message":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "Framed-IP-Address":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "Framed-IP-Netmask":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "Framed-Pool":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "Service-Type":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "Session-Timeout":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "Idle-Timeout":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "WISPr-Redirection-URL":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "WISPr-Bandwidth-Max-Up":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "WISPr-Bandwidth-Max-Down":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "WISPr-Session-Terminate-Time":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "ChilliSpot-Max-Input-Octets":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "ChilliSpot-Max-Output-Octets":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "ChilliSpot-Max-Total-Octets":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "ChilliSpot-UAM-Allowed":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "ChilliSpot-MAC-Allowed":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "ChilliSpot-MAC-Interval":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "MikroTik-Rate-Limit":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "Ascend-Xmit-Rate":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;
		case "Ascend-Data-Rate":
			$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
			break;

	}


	return $table;


}



?>
