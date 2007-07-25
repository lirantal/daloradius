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

	$attributesArray = array('Max-All-Session' => 'seconds',
	                         'Session-Timeout' => 'seconds'
	                        );

	echo "<h4> Session Attributes </h4>";

	$cnt = 0;
	foreach ( $attributesArray as $attrib => $help ) {
		echo <<<EOA
			<input type="checkbox" onclick="javascript:toggleShowDiv('attributes$attrib')">
			<b>$attrib</b><br/>
			<div id="attributes$attrib" style="display:none;visibility:visible" >
					<input value="" id="$attrib" name="$attrib">
EOA;
		drawSelectSeconds($attrib, $cnt);
		echo "
		<br/><br/>
		</div>
		";

	$cnt++;
	}

}




function drawSelectSeconds($attribute, $counter) {

	echo <<<EOS
		<select onChange="javascript:setText(this.id,'$attribute')" id="option$counter">
		<option value="86400">1day(s)</option>
		<option value="259200">3day(s)</option>
		<option value="604800">1week(s)</option>
		<option value="1209600">2week(s)</option>
		<option value="1814400">3week(s)</option>
		<option value="2592000">1month(s)</option>
		<option value="5184000">2month(s)</option>
		<option value="7776000">3month(s)</option>
		</select>
EOS;

}


function checkTables($attribute) {
/*
* @param $attribute	The attribute name, Session-Timeout for example
* @return $table		The table name, either radcheck or radreply
*/
	$table = $configValues['CONFIG_DB_TBL_RADCHECK'];
	
	switch ($attribute) {
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
	}

	return $table;


}



?>
