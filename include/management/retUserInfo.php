<?php

include('../../library/checklogin.php');
include_once('pages_common.php');

if (isset($_GET['retBandwidthInfo'])) {

	$divContainer = $_GET['divContainer'];				// get target div id
	$username = $_GET['username'];

	include('../../library/opendb.php');

	$sql = "SELECT SUM(AcctInputOctets) AS Upload, SUM(AcctOutputOctets) AS Download FROM ".
		$configValues['CONFIG_DB_TBL_RADACCT']." WHERE UserName='".
		$dbSocket->escapeSimple($username)."'";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

	$upload = toxbyte($row['Upload']);
	$download = toxbyte($row['Download']);

	if ($upload <= 0)
		$upload = 0;
	
	if ($download <= 0)
		$download = 0;
	

	printqn("
		var divContainer = document.getElementById('{$divContainer}');
		divContainer.innerHTML = '<span style=\"font-weight:normal;\">Upload:</span> $upload <br/>".
						"<span style=\"font-weight:normal;\">Download: </span> $download';
	");


}



?>
