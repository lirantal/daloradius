<?php

    include 'library/opendb.php';
    $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE UserName='$operator'";
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

	$currFile = basename($_SERVER['SCRIPT_NAME']);
	$currFile = str_replace("-", "_", $currFile);
	$currFile = str_replace(".php", "", $currFile);

    $test = $row[$currFile];

	if (!( (strcasecmp($test, "y") == 0) || (strcasecmp($test, "yes") == 0) || (strcasecmp($test, "on") == 0)   )) {
//		echo "<br/><br/> permission denied! <br/><br/>";
		header('Location: msg-error-permissions.php');
		exit;
	}

    include 'library/closedb.php';

 




?>
