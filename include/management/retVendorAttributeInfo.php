<?php

include('../../library/checklogin.php');
include_once('pages_common.php');

if (isset($_GET['retAttributeInfo'])) {

	$divContainer = $_GET['divContainer'];				// get target div id
	$attribute = $_GET['attribute'];

	include('../../library/opendb.php');

	$sql = "SELECT RecommendedTooltip FROM ".$configValues['CONFIG_DB_TBL_DALODICTIONARY']. 
		" WHERE Attribute='".$dbSocket->escapeSimple($attribute)."'";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

	$desc = $row['RecommendedTooltip'];

	printqn("
		var divContainer = document.getElementById('{$divContainer}');
		divContainer.innerHTML = 'Description: <span style=\"font-weight:normal;\"> $desc </span> <br/>';
	");


}

?>
