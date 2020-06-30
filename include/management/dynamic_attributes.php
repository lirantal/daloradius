<?php

include('../../library/checklogin.php');

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
			new Option('".trim($row[0])."','".trim($row[0])."');\n";
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
			new Option('".trim($row[0])."','".trim($row[0])."');\n";
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
	$RecommendedOP = trim($row[0]);
	$RecommendedTable = trim($row[1]);
	$RecommendedTooltip = trim($row[2]);
	$type = trim($row[3]);
	$RecommendedHelper = trim($row[4]);




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

		case "framedprotocol":
			drawFramedProtocol($num);
			break;

		case "volumebytes":
			drawBytes($num);
			break;

		case "bitspersecond":
			drawBitPerSecond($num);
			break;
			
		case "mikrotikRateLimit":
			mikrotikRateLimit($num);
			break;

		case "kbitspersecond":
			drawKBitPerSecond($num);
			break;

	}
	/*******************************************************************************************************/



	/*******************************************************************************************************/
	/* RecommendedTooltip
	/* setting the tooltip 
	/*******************************************************************************************************/
	echo "objTooltip.innerHTML = \"<b>Description:</b> ".str_replace("\"", "\\\"", $RecommendedTooltip)."\";";
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
	$currYear = date('Y', time());

        echo <<<EOF
	objHelper.innerHTML = "<img src='library/js_date/calendar.gif' onClick=\"showChooser(this, '$inputId', 'chooserSpan$num', 1950, $currYear+5, 'Y-m-d H:i:s', true);\">";

EOF;

}



function drawHelperDate($num) {

	$inputId = "dictValues".$num;
	$currYear = date('Y', time());

        echo <<<EOF
	objHelper.innerHTML = "<img src='library/js_date/calendar.gif' onClick=\"showChooser(this, '$inputId', 'chooserSpan$num', 1950, $currYear+5, 'd M Y', false);\">";

EOF;

}

function drawBytes($num) {

        $inputId = "dictValues".$num;

        echo <<<EOF
        objHelper.innerHTML = "<select onClick=\"setStringText(this.id,'$inputId');\" id='drawBytes$num' "+
                                "style='width: 100px' class='form'>"+
                                "<option value=''>Select...</option>"+
                                "<option value='10485760'>10Mb</option>"+
                                "<option value='52428800'>50Mb</option>"+
                                "<option value='104857600'>100Mb</option>"+
                                "<option value='524288000'>500Mb</option>"+
                                "<option value='1073741824'>1Gb</option>"+
                                "<option value='2147483648'>2Gb</option>"+
                                "<option value='4294967296'>4Gb</option>"+
                                "<option value='8589934592'>8Gb</option>"+
                                "<option value='12884901888'>12Gb</option>"+
                                "<option value='17179869184'>16Gb</option>"+
                              "</select>";

EOF;

}


function drawFramedProtocol($num) {

        $inputId = "dictValues".$num;

        echo <<<EOF
        objHelper.innerHTML = "<select onClick=\"setStringText(this.id,'$inputId');\" id='drawFramedProtocol$num' "+
                                "style='width: 100px' class='form'>"+
                                "<option value=''>Select...</option>"+
                                "<option value='PPP'>PPP</option>"+
                                "<option value='SLIP'>SLIP</option>"+
                                "<option value='ARAP'>ARAP</option>"+
                                "<option value='Gandalf-SLML'>Gandalf-SLML</option>"+
                                "<option value='Xylogics-IPX-SLIP'>Xylogics-IPX-SLIP</option>"+
                                "<option value='X.75-Synchronous'>X.75-Synchronous</option>"+
                                "<option value='PPTP'>PPTP</option>"+
                                "<option value='GPRS-PDP-Context'>GPRS-PDP-Context</option>"+
                              "</select>";

EOF;

}



function drawBitPerSecond($num) {

        $inputId = "dictValues".$num;

        echo <<<EOF
        objHelper.innerHTML = "<select onClick=\"setStringText(this.id,'$inputId');\" id='drawBitPerSecond$num' "+
                                "style='width: 100px' class='form'>"+
                                "<option value=''>Select...</option>"+
                                "<option value='32000'>32kbps</option>"+
                                "<option value='64000'>64kbps</option>"+
                                "<option value='128000'>128kbps</option>"+
                                "<option value='256000'>256kbps</option>"+
                                "<option value='512000'>512kbps</option>"+
                                "<option value='750000'>750kbps</option>"+
                                "<option value='1048576'>1mbps</option>"+
                                "<option value='1572864'>1.5mbps</option>"+
                                "<option value='2097152'>2mbps</option>"+
                                "<option value='3145728'>3mbps</option>"+
                                "<option value='5242880'>5mbps</option>"+
                                "<option value='8388608'>8mbps</option>"+
                                "<option value='10485760'>10mbps</option>"+
                              "</select>";

EOF;

}


function mikrotikRateLimit($num) {

        $inputId = "dictValues".$num;

        echo <<<EOF
        objHelper.innerHTML = "<select onClick=\"setStringText(this.id,'$inputId');\" id='drawBitPerSecond$num' "+
                                "style='width: 100px' class='form'>"+
                                "<option value=''>Select...</option>"+
                                "<option value='128k/128k'>128k/128k</option>"+
                                "<option value='128k/256k'>128k/256k</option>"+
                                "<option value='128k/512k'>128k/512k</option>"+
                                "<option value='128k/1M'>128k/1M</option>"+
                                "<option value='256k/256k'>256k/256k</option>"+
                                "<option value='256k/1M'>256k/1M</option>"+
                                "<option value='512k/512k'>512k/512k</option>"+
                                "<option value='512k/1M'>512k/1M</option>"+
                                "<option value='512k/2M'>512k/2M</option>"+
                                "<option value='1M/1M'>1M/1M</option>"+
                                "<option value='1M/2M'>1M/2M</option>"+
                                "<option value='2M/2M'>2M/2M</option>"+
                                "<option value='1M/5M'>1M/5M</option>"+
                              "</select>";

EOF;

}


function drawKBitPerSecond($num) {

        $inputId = "dictValues".$num;

        echo <<<EOF
        objHelper.innerHTML = "<select onClick=\"setStringText(this.id,'$inputId');\" id='drawKBitPerSecond$num' "+
                                "style='width: 100px' class='form'>"+
                                "<option value=''>Select...</option>"+
                                "<option value='32'>32kbps</option>"+
                                "<option value='64'>64kbps</option>"+
                                "<option value='128'>128kbps</option>"+
                                "<option value='256'>256kbps</option>"+
                                "<option value='512'>512kbps</option>"+
                                "<option value='750'>750kbps</option>"+
                                "<option value='1000'>1mbps</option>"+
                                "<option value='1500'>1.5mbps</option>"+
                                "<option value='2500'>2mbps</option>"+
                                "<option value='3000'>3mbps</option>"+
                                "<option value='5000'>5mbps</option>"+
                                "<option value='8000'>8mbps</option>"+
                                "<option value='10000'>10mbps</option>"+
                              "</select>";

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
