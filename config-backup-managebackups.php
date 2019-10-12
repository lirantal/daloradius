<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	$logAction = "";
	$logDebugSQL = "";

	if (isset($_GET['file']))
		$file = $_GET['file'];

	if (isset($_GET['action']))
		$action = $_GET['action'];

	if ( (isset($_GET['file'])) && (isset($_GET['action'])) && ($_GET['action'] == "download") ) {

		include_once('library/config_read.php');

		$isError = 0;

		$filePath = $configValues['CONFIG_PATH_DALO_VARIABLE_DATA']."/backup/";
		$fileName = $filePath.$file;

		if (is_dir($filePath)) {

			if (is_readable($fileName)) {

				$fileDownload = file_get_contents($fileName);

		        header("Content-type: txt/html");
		        header("Content-disposition: csv; filename=".basename($fileName)."; size=" . strlen($fileDownload) );
		        print $fileDownload;

				exit;

			} else
				$isError++;
		} else
			$isError++;

		if ($isError > 0) {
			$failureMsg = "Failed downloading backup file <b>$fileName</b> from web server, please check file availability and permissions";
			$logAction .= "Failed downloading backup file [$fileName] from web server on page: ";
		}

	}






	if ( (isset($_GET['file'])) && (isset($_GET['action'])) && ($_GET['action'] == "rollback") ) {

		include_once('library/config_read.php');

		$isError = 0;

                $filePath = $configValues['CONFIG_PATH_DALO_VARIABLE_DATA']."/backup/";
		$fileName = $filePath.$file;
		$baseFile = basename($fileName);

                if (is_dir($filePath)) {
			if (is_readable($fileName)) {

			$tableNames = "";
			$fileRollback = file_get_contents($fileName);

			include 'library/opendb.php';

			$rollBackQuery = preg_split("/\n\n\n/", $fileRollback);	// when we created the backup file we splitted every table INSERT INTO
										// entry with a tripple newline (\n\n\n) 3 bytes characteres and so to insert
										// these again we split into an array each INSERT query because Pear DB
										// can't handle multiple INSERTs in a concatenated string
			foreach($rollBackQuery as $query) {


				$tableName = substr($query, 12, 20);		// we take 20 chars more as the possible table name, it should not be
										// that long anyway
				$tableName = substr($tableName, 0, stripos($tableName, ' '));
										// we extract the <table> from the string: INSERT INTO <table>

				if ($tableName != "") {

					$tableNames .= "$tableName, ";

					$sql = "DELETE FROM $tableName";
					$res = $dbSocket->query($sql);

					if (DB::isError ($res)) {
						$isError++;
						break;
					}

					$sql = $query;					// this is a large SQL query, hopefully database can handle it without
											// overflowing
					$res = $dbSocket->query($sql);

					if (DB::isError ($res))
						$isError++;

				} // if $tableName is not empty

			}

			$tableNames = substr($tableNames, 0, -2);			// fixing up variable

			include 'library/closedb.php';

			} else
				$isError++;
		} else
			$isError++;


		if ($isError > 0) {
			$failureMsg = "Failed rolling-back from file <b>$fileName</b>, please check file availability and permissions";
			$logAction .= "Failed rolling-back from file [$fileName] on page: ";
		} else {
			$successMsg = "Successfully rolled-back database from source file <b>$baseFile</b><br/> Affected tables were: <b>$tableNames</b>";
			$logAction .= "Successfully rolled-back database from source file [$baseFile] on page: ";
		}



	}


	include_once('library/config_read.php');
    $log = "visited page: ";

?>

<?php
        include_once ("library/tabber/tab-layout.php");
?>

<?php

    include ("menu-config-backup.php");

?>


		<div id="contentnorightbar">

				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','configbackupmanagebackups.php') ?>
				<h144>&#x2754;</h144></a></h2>
                <div id="helpPage" style="display:none;visibility:visible" >
					<?php echo t('helpPage','configbackupmanagebackups') ?>
					<br/>
				</div>
                <?php
					include_once('include/management/actionMessages.php');
                ?>

				<form name="managebackups" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">





        <table border='0' class='table1'>

                <thead>
                        <tr>
				<th colspan='10' align='left'>
				</th>
			</tr>
                </thead>

		<tr>
		<td> Time of Creation </td>
		<td> Filename </td>
		<td> File size </td>
		<td> Perform Action </td>
		</tr>

	<?php

		include_once('library/config_read.php');

		$filePath = $configValues['CONFIG_PATH_DALO_VARIABLE_DATA']."/backup";

		if (is_dir($filePath)) {
			$dirHandler = opendir($filePath);
			while ($file = readdir($dirHandler)) {
				if ( ($file != '.') && ($file != '..') && ($file != '.svn') ) {

				list($junk, $date, $time) = explode("-", $file);

				$fileDate = substr($date, 0, 4) . "-" . substr($date, 4, 2) . "-" . substr($date, 6, 2);
				$fileTime = substr($time, 0, 2) . ":" . substr($time, 2, 2) . ":" . substr($time, 4, 2);

				$fileSize = filesize($filePath."/".$file);

				echo "<tr>";
				echo "<td>";
					echo $fileDate ." ". $fileTime;
				echo "</td>";

				echo "<td>";
					echo $file;
				echo "</td>";

				echo "<td>";
					echo $fileSize . " bytes";
				echo "</td>";

				echo "<td>";
					echo "<a class='tablenovisit' href='?file=$file&action=download' >".t('all','Download')."</a>";

					echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

					echo "<a class='tablenovisit' href='#' onClick=\"javascript:backupRollback('$file');\">".t('all','Rollback')."</a>";
				echo "</td>";

				}
			}
		}



	?>


	</table>



				</form>


<?php
        include('include/config/logging.php');
?>

		</div>

		<div id="footer">

								<?php
        include 'page-footer.php';
?>

		</div>

</div>
</div>


</body>
</html>
