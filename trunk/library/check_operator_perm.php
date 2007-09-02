<?php

    include 'library/opendb.php';
    $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE UserName='$operator'";
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

	$currFile = basename($_SERVER['SCRIPT_NAME']);
	$currFile = str_replace("-", "_", $currFile);
	$currFile = str_replace(".php", "", $currFile);

	// the importance of the following is not to be discarded.
	// the following tests if the page is defined and valid in the  include/management/operator_tables.php array and if it isn't
	// it will force the page to not be displayed. meaning that all pages (for example newer pages) must be defined in that array
	// otherwise they will not be accessible.
	isset($row[$currFile]) ? $test = $row[$currFile] : $test="no";
	
	if (!( (strcasecmp($test, "y") == 0) || (strcasecmp($test, "yes") == 0) || (strcasecmp($test, "on") == 0)   )) {
//		echo "<br/><br/> permission denied! <br/><br/>";
		header('Location: msg-error-permissions.php');
		exit;
	}

    include 'library/closedb.php';

 




?>
