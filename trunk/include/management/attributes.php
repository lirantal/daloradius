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
	 'Max-All-Session' => 'seconds',
	 'Session-Timeout' => 'seconds',
	 'Idle-Timeout' => 'seconds'
	 );

	 
	$arrayNasAttr = array(
	 'Calling-Station-Id' => 'none',
	 'Called-Station-Id' => 'none'
	 );	 
	 
	 
	$arrayWISPrAttr = array(
	 'WISPr-Redirection-URL' => 'none',
	 'WISPr-Bandwidth-Max-Up' => 'speed',
	 'WISPr-Bandwidth-Max-Down' => 'speed',
	 'WISPr-Session-Terminate-Time' => 'date'
	 );	 
	 
	 
	echo "<br/><br/><h4> Session Attributes </h4>";
	$cnt = 0;
	foreach ( $arraySessionAttr as $attrib => $help ) {
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
	
	echo "<br/><br/><h4> NAS Attributes </h4>";
	
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
	

	
	echo "<br/><br/><h4> WISPr Attributes </h4>";
	
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
		<select onChange="javascript:setText(this.id,'$attribute')" id="option$counter">
		<option value="128000">128kbit</option>
		<option value="256000">256kbit</option>
		<option value="512000">512kbit</option>
		<option value="1048576">1mbit</option>
		<option value="1572864">1.5mbit</option>
		<option value="2097152">2mbit</option>
		<option value="3145728">3mbit</option>
		<option value="10485760">10mbit</option>
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
    include ('library/config_read.php');
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
