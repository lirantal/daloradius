<?php
/*********************************************************************
 *
 * Filename: fileExport.php
 * Author: Liran Tal <liran.tal@gmail.com>
 *
 * Description:
 * The purpose of this extension is to handle exports of different
 * kinds like CSV andother formats to the user's browser so that
 * they can download a local copy of the tables listing mostly
 *********************************************************************/

if (isset($_REQUEST['csv_output'])) {

	$csv_output = $_REQUEST['csv_output'];
	$csv_formatted = str_replace("||", "\r\n", $csv_output);

        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: csv; filename=export_" . date("Ymd") . ".csv; size=" . strlen($csv_formatted));
        //header("Content-disposition: csv; filename=document_; size=" . strlen($csv_output));
	print $csv_formatted;
	exit;

}

?>
