<?php

/*
 * getVendorsList is set to yes when the user clicks on the Vendor select box
 * upon which the javascript code executes a call with this value which we catch
 * here and populate the Vendors select box with the available Vendors found from
 * the database
 */
if(isset($_GET['getVendorsList'])) {

	include '../../library/opendb.php';

	$sql = "SELECT distinct(Vendor) as Vendor FROM dictionary WHERE Vendor>'' ORDER BY Vendor ASC";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
		echo "objVendors.options[objVendors.options.length] = 
			new Option('$row[0]','$row[0]');\n";
	}

	include '../../library/closedb.php';

}

/*
 * vendorAttributes is set to the vendor name which the user has chosen and passed
 * to us so that we populate the Attributes select box with the available attributes
 * found from the database for a specific vendor.
 */
if(isset($_GET['vendorAttributes'])) {
  
	$vendor = $_GET['vendorAttributes'];

	include '../../library/opendb.php';

	$sql = "SELECT attribute FROM dictionary WHERE Vendor='".
		$dbSocket->escapeSimple($vendor)."' AND Value IS NULL";
        $res = $dbSocket->query($sql);

		echo "objAttributes.options[objAttributes.options.length] = 
			new Option('Select Attribute...','');\n";

        while($row = $res->fetchRow()) {
		echo "objAttributes.options[objAttributes.options.length] = 
			new Option('$row[0]','$row[0]');\n";
	}

	include '../../library/closedb.php';
}


/*
 * getValuesForAttribute is set to the attribute's name upon which we expect to
 * run a sql query and fetch all the available pre-defined values availabe for 
 * this specific attribute from the database. If none, we simply reset the 
 * input box to null.
 * 
 * at this point we also populate the other fields such as OP with the default OP
 * found from the database (optional) and all the other possible options for OP.
 * The same goes for the Table field.
 * 
 * The tooltip and type are fields (text fields in html) which are also grabbed from
 * the database, the tooltip is some helpful information about the attribute
 * and the type is the attribute's type (string, integer, ipaddr, etc)
 */
if(isset($_GET['getValuesForAttribute'])) {

	$attribute = $_GET['getValuesForAttribute'];
	$dictValueId = $_GET['dictValueId'];
	$num = $_GET['instanceNum'];

	include '../../library/opendb.php';

	$sql = "SELECT RecommendedOP,RecommendedTable,RecommendedTooltip,type,RecommendedHelper FROM dictionary 
		WHERE Attribute='".$dbSocket->escapeSimple($attribute)."'";

	$res = $dbSocket->query($sql);
	$row = $res->fetchRow();
	$RecommendedOP = $row[0];
	$RecommendedTable = $row[1];
	$RecommendedTooltip = $row[2];
	$type = $row[3];
	$RecommendedHelper = $row[4];




	/*******************************************************************************************************/
	/* RecommendedOP
	/* set the first option of the dictOP select box to be the default recommended OP from the
	/* dictionary table
	/*******************************************************************************************************/
	if (isset($RecommendedOP)) {
		echo "objOP.options[objOP.options.length] = new Option('$RecommendedOP',
		'$RecommendedOP');\n";
	}
	populateOPs(); 	//then we populate the dictOP select box with the normal possible values for it:
	/*******************************************************************************************************/



	/*******************************************************************************************************/
	/* RecommendedTable
	/* next up we set as the first option of the select box the default target table for this attribute
	/*******************************************************************************************************/
	if (isset($RecommendedTable)) {
		echo "objTable.options[objTable.options.length] = new Option('$RecommendedTable','$RecommendedTable');\n";
	}
	populateTables();		//and ofcourse populate it also with the possible tables
	/*******************************************************************************************************/



	/*******************************************************************************************************/
	/* setting the dictValue to be empty
	/*******************************************************************************************************/
	echo "objValues.value = '';\n";
	/*******************************************************************************************************/



	/*******************************************************************************************************/
	/* RecommendedHelper
	/* this draws the appropriate helper function/for the attribute using the innerHTML method to the 
	/* html <span> element within the dynamic attribute boxes
	/*******************************************************************************************************/
	switch($RecommendedHelper) {

		case "datetime":
			drawHelperDateTime($num);
			break;

		case "date":
			drawHelperDate($num);
			break;

		case "authtype":
			drawAuthType($num);
			break;

		case "servicetype":
			drawServiceType($num);
			break;



	}
	/*******************************************************************************************************/



	/*******************************************************************************************************/
	/* RecommendedTooltip
	/* setting the tooltip 
	/*******************************************************************************************************/
	echo "objTooltip.innerHTML = \"<b>Description:</b> $RecommendedTooltip\";";
	/*******************************************************************************************************/



	/*******************************************************************************************************/
	/* Format type
	/* setting the format
	/*******************************************************************************************************/
	echo "objType.innerHTML = \"<b>Type:</b> $type\";";
	/*******************************************************************************************************/


	include '../../library/closedb.php';

}




function populateTables() {
	echo "if (objTable.type == \"select-one\") objTable.options[objTable.options.length] = new Option('check','check');\n";
	echo "if (objTable.type == \"select-one\") objTable.options[objTable.options.length] = new Option('reply','reply');\n";
}



function populateOPs() {
	echo "objOP.options[objOP.options.length] = new Option('=','=');\n";
	echo "objOP.options[objOP.options.length] = new Option('==','==');\n";
	echo "objOP.options[objOP.options.length] = new Option(':=',':=');\n";
	echo "objOP.options[objOP.options.length] = new Option('+=','+=');\n";
	echo "objOP.options[objOP.options.length] = new Option('!=','!=');\n";
	echo "objOP.options[objOP.options.length] = new Option('>','>');\n";
	echo "objOP.options[objOP.options.length] = new Option('>=','>=');\n";
	echo "objOP.options[objOP.options.length] = new Option('<','<');\n";
	echo "objOP.options[objOP.options.length] = new Option('<=','<=');\n";
	echo "objOP.options[objOP.options.length] = new Option('=~','=~');\n";
	echo "objOP.options[objOP.options.length] = new Option('!~','!~');\n";
	echo "objOP.options[objOP.options.length] = new Option('=*','=*');\n";
	echo "objOP.options[objOP.options.length] = new Option('!*','!*');\n";
}



function drawHelperDateTime($num) {

	$inputId = "dictValues".$num;

        echo <<<EOF
	objHelper.innerHTML = "<img src='library/js_date/calendar.gif' onClick=\"showChooser(this, '$inputId', 'chooserSpan$num', 1950, 2010, 'Y-m-d H:i:s', true);\">";

EOF;

}



function drawHelperDate($num) {

	$inputId = "dictValues".$num;

        echo <<<EOF
	objHelper.innerHTML = "<img src='library/js_date/calendar.gif' onClick=\"showChooser(this, '$inputId', 'chooserSpan$num', 1950, 2010, 'd M Y', false);\">";

EOF;

}



function drawAuthType($num) {

	$inputId = "dictValues".$num;

        echo <<<EOF
	objHelper.innerHTML = "<select onClick=\"setStringText(this.id,'$inputId');\" id='drawAuthType$num' "+
				"style='width: 100px' class='form'>"+
				"<option value=''>Select...</option>"+
				"<option value='Local'>Local</option>"+
				"<option value='System'>System</option>"+
				"<option value='Accept'>Accept</option>"+
				"<option value='Reject'>Reject</option>"+
				"<option value='SecurID'>SecurID</option>"+
				"<option value='Crypt-Local'>Crypt-Local</option>"+
				"<option value='ActivCard'>ActivCard</option>"+
				"<option value='EAP'>EAP</option>"+
				"<option value='PAP'>PAP</option>"+
				"<option value='CHAP'>CHAP</option>"+
				"<option value='MS-CHAP'>MS-CHAP</option>"+
				"<option value='PAM'>PAM</option>"+
				"<option value='Kerberos'>Kerberos</option>"+
				"<option value='CRAM'>CRAM</option>"+
				"<option value='NS-MTA-MD5'>NS-MTA-MD5</option>"+
				"<option value='SMB'>SMB</option>"+
				"<option value='Unix'>Unix</option>"+
				"<option value='None'>None</option>"+
				"<option value='ARAP'>ARAP</option>"+
			      "</select>";

EOF;

}





function drawServiceType($num) {

	$inputId = "dictValues".$num;

        echo <<<EOF
	objHelper.innerHTML = "<select onClick=\"setStringText(this.id,'$inputId');\" id='drawServiceType$num' "+
				"style='width: 100px' class='form'>"+
				"<option value=''>Select...</option>"+
				"<option value='Login-User'>Login-User</option>"+
				"<option value='Framed-User'>Framed-User</option>"+
				"<option value='Callback-Login-User'>Callback-Login-User</option>"+
				"<option value='Callback-Framed-User'>Callback-Framed-User</option>"+
				"<option value='Outbound-User'>Outbound-User</option>"+
				"<option value='Administrative-User'>Administrative-User</option>"+
				"<option value='NAS-Prompt-User'>NAS-Prompt-User</option>"+
				"<option value='Authenticate-Only'>Authenticate-Only</option>"+
				"<option value='Callback-NAS-Prompt'>Callback-NAS-Prompt</option>"+
				"<option value='Call-Check'>Call-Check</option>"+
				"<option value='Callback-Administrative'>Callback-Administrative</option>"+
				"<option value='Sip-session'>Sip-session</option>"+
				"<option value='Annex-Authorize-Only'>Annex-Authorize-Only</option>"+
				"<option value='Annex-Framed-Tunnel'>Annex-Framed-Tunnel</option>"+
				"<option value='Authorize-Only'>Authorize-Only</option>"+
				"<option value='Shell-User'>Shell-User</option>"+
				"<option value='Dialback-Login-User'>Dialback-Login-User</option>"+
				"<option value='Dialback-Framed-User'>Dialback-Framed-User</option>"+
				"<option value='Login'>Login</option>"+
				"<option value='Framed'>Framed</option>"+
				"<option value='Callback-Login'>Callback-Login</option>"+
				"<option value='Callback-Framed'>Callback-Framed</option>"+
				"<option value='Exec-User'>Exec-User</option>"+
				"<option value='Sip-Session'>Sip-Session</option>"+
				"<option value='Dialout-Framed-User'>Dialout-Framed-User</option>"+
			      "</select>";

EOF;

}











?>
