<?php

include('../../library/checklogin.php');
include_once('pages_common.php');

if (isset($_GET['retHotspotGeneralStat'])) {

	$divContainer = $_GET['divContainer'];				// get target div id
	$hotspot = $_GET['hotspot'];

	include('../../library/opendb.php');

        $sql = "SELECT ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
                ".name AS hotspot, count(distinct(UserName)) AS uniqueusers, count(radacctid) AS totalhits, ".
                " avg(AcctSessionTime) AS avgsessiontime, sum(AcctSessionTime) AS totaltime, ".
                " avg(AcctInputOctets) AS avgInputOctets, sum(AcctInputOctets) AS sumInputOctets, ".
                " avg(AcctOutputOctets) AS avgOutputOctets, sum(AcctOutputOctets) AS sumOutputOctets ".
                " FROM ".
                $configValues['CONFIG_DB_TBL_RADACCT']." JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
                " on (".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid LIKE ".
                $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac) WHERE ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
		".name='".$dbSocket->escapeSimple($hotspot)."' ".
		" GROUP BY ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
                ".name ;";

	$res = $dbSocket->query($sql);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

	$sumUpload = toxbyte($row['sumInputOctets']);
	$sumDownload = toxbyte($row['sumOutputOctets']);
	$sumHits = $row['totalhits'];

	if ($sumUpload <= 0)
		$sumUpload = 0;

	if ($sumDownload <= 0)
		$sumDownload = 0;

	if ($sumHits <= 0)
		$sumHits = 0;

	printqn("
		var divContainer = document.getElementById('{$divContainer}');
		divContainer.innerHTML = '<span style=\"font-weight:normal;\">Total Uploads:</span> $sumUpload <br/> ".
					" <span style=\"font-weight:normal;\">Total Downloads:</span> $sumDownload <br/> ".
					" <span style=\"font-weight:normal;\">Total Hits:</span> $sumHits'
	");

}



?>
