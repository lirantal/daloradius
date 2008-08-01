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

	isset($_POST['radacct']) ? $radacctTable = $_POST['radacct'] : $radacctTable = 'yes';
	isset($_POST['radcheck']) ? $radcheckTable = $_POST['radcheck'] : $radcheckTable = 'yes';
 	isset($_POST['radpostauth']) ? $radpostauth = $_POST['radpostauth'] : $radpostauth = 'yes';
	isset($_POST['radgroupcheck']) ? $radgroupcheckTable = $_POST['radgroupcheck'] : $radgroupcheckTable = 'yes';
	isset($_POST['radgroupreply']) ? $radgroupreplyTable = $_POST['radgroupreply'] : $radgroupreplyTable = 'yes';
	isset($_POST['radusergroup']) ? $radusergroupTable = $_POST['radusergroup'] : $radusergroupTable = 'yes';
	isset($_POST['radpostauth']) ? $radpostauthTable = $_POST['radpostauth'] : $radpostauthTable = 'yes';
	isset($_POST['ippool']) ? $ippoolTable = $_POST['ippool'] : $ippoolTable = 'yes';
	isset($_POST['nas']) ? $nasTable = $_POST['nas'] : $nasTable = 'yes';
	isset($_POST['dictionary']) ? $dictionaryTable = $_POST['dictionary'] : $dictionaryTable = 'yes';

	isset($_POST['hotspots']) ? $dalohotspotsTable = $_POST['hotspots'] : $dalohotspotsTable = 'yes';
	isset($_POST['operators']) ? $dalooperatorsTable = $_POST['operators'] : $dalooperatorsTable = 'yes';
	isset($_POST['proxys']) ? $daloproxysTable = $_POST['proxys'] : $daloproxysTable = 'yes';
	isset($_POST['realms']) ? $dalorealmsTable = $_POST['realms'] : $dalorealmsTable = 'yes';
	isset($_POST['rates']) ? $daloratesTable = $_POST['rates'] : $daloratesTable = 'yes';
	isset($_POST['userinfo']) ? $dalouserinfoTable = $_POST['userinfo'] : $dalouserinfoTable = 'yes';


	if (isset($_POST['submit'])) {

		include 'library/opendb.php';
		$backupQuery = "";

		$sqlQuery = "";
		$isError = 0;

		foreach($_POST as $element=>$value) {

			if ($element == "submit")
				continue;

			switch ($element) {
				case "radacct":
					if ($value == "yes")
						$table = $configValues['CONFIG_DB_TBL_RADACCT'];
					break;
				case "radreply":
					if ($value == "yes")
						$table = $configValues['CONFIG_DB_TBL_RADREPLY'];
					break;
				case "radcheck":
					if ($value == "yes")
						$table = $configValues['CONFIG_DB_TBL_RADCHECK'];
					break;
				case "radusergroup":
					if ($value == "yes")
						$table = $configValues['CONFIG_DB_TBL_RADUSERGROUP'];
					break;
				case "radgroupreply":
					if ($value == "yes")
						$table = $configValues['CONFIG_DB_TBL_RADGROUPREPLY'];
					break;
				case "radgroupcheck":
					if ($value == "yes")
						$table = $configValues['CONFIG_DB_TBL_RADGROUPCHECK'];
					break;
				case "radpostauth":
					if ($value == "yes")
						$table = $configValues['CONFIG_DB_TBL_RADPOSTAUTH'];
					break;
				case "ippool":
					if ($value == "yes")
						$table = $configValues['CONFIG_DB_TBL_RADIPPOOL'];
					break;
				case "nas":
					if ($value == "yes")
						$table = $configValues['CONFIG_DB_TBL_RADNAS'];
					break;
				case "hotspots":
					if ($value == "yes")
						$table = $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'];
					break;
				case "operators":
					if ($value == "yes")
						$table = $configValues['CONFIG_DB_TBL_DALOOPERATOR'];
					break;
				case "rates":
					if ($value == "yes")
						$table = $configValues['CONFIG_DB_TBL_DALORATES'];
					break;
				case "userinfo":
					if ($value == "yes")
						$table = $configValues['CONFIG_DB_TBL_DALOUSERINFO'];
					break;
				case "dictionary":
					if ($value == "yes")
						$table = $configValues['CONFIG_DB_TBL_DALODICTIONARY'];
					break;
				case "realms":
					if ($value == "yes")
						$table = $configValues['CONFIG_DB_TBL_DALOREALMS'];
					break;
				case "proxys":
					if ($value == "yes")
						$table = $configValues['CONFIG_DB_TBL_DALOPROXYS'];
					break;
			}

			if (isset($table)) {

				$sqlTableQuery = "INSERT INTO $table (";
	
				$colLength = 0;

				$sql = "SELECT * FROM $table";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				if (DB::isError ($res)) {
					$isError++;
					break;
				}

				if ($res->numRows() == 0)
					continue;

				$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
				foreach($row as $key=>$value) {				// $key is the table field and $value is the field's value
					$sqlTableQuery .= "$key, ";
					$colLength++;
				}
	
				$sqlTableQuery = substr($sqlTableQuery,0,-2);
				$sqlTableQuery .= ") VALUES ";
	
				$sql = "SELECT * FROM $table";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				if (DB::isError ($res)) {
					$isError++;
					break;
				}
	
				$i = 0;
				$currRow = "";
	
				while($row = $res->fetchRow()) {
	
					$currRow = " (";
					for ($i = 0; $i < $colLength; $i++) {
						$currRow .= "'$row[$i]',";
					}
					$currRow = substr($currRow,0,-1);
					$currRow .= ")";
					$sqlTableQuery .= "$currRow,";
				}
	
				$sqlTableQuery = substr($sqlTableQuery,0,-1);
				$sqlTableQuery .= ";\n";
				$sqlQuery .= $sqlTableQuery;
			}

			unset($table);

		}


		if ($isError > 0) {
			$failureMsg = "Failed creating backup due to database error, check your database settings";
			$logAction .= "Failed creating backup due to database error on page: ";
		} else {


			$filePrefix = "backup";
			$fileDate = date("Ymd-His");
			$filePath = $configValues['CONFIG_PATH_DALO_VARIABLE_DATA']."/backup/";
			$fileName = $filePath.$filePrefix."-".$fileDate.".sql";

			if ( (file_exists($filePath)) && (is_writable($filePath)) ) {
				$fh = fopen($fileName, "w");
				fwrite($fh, "$sqlQuery");
				fclose($fh);

				$successMsg = "Successfully created backup";
				$logAction .= "Successfully created backup file [$fileName] on page: ";
			} else {
				$failureMsg = "Failed creating backup due to directory/file permissions, check that the webserver user has access ".
						"to create the following file: <b>$fileName</b>";
				$logAction .= "Failed creating backup due to directory/file permissions on page: ";
			}
		}
	


		include 'library/closedb.php';

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
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['configbackupcreatebackups.php'] ?>
				<h144>+</h144></a></h2>
                <div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['configbackupcreatebackups'] ?>
					<br/>
				</div>
                <?php
					include_once('include/management/actionMessages.php');
                ?>

				<form name="createbackups" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

     <div class="tabbertab" title="<?php echo $l['title']['FreeRADIUSTables']; ?>">
        <br/>

        <fieldset>

                <h302> <?php echo $l['title']['Backups']; ?> </h302>
		<br/>

                <label class='form'>Select database tables to backup:</label>

                <ul>

                <li class='fieldset'>
                <label for='backup_radacct' class='form'>radacct</label> 
		<select class='form' name="radacct">
			<option value="<?php echo $radacctTable ?>"><?php echo $radacctTable ?></option>
			<option value="">  </option>
			<option value="no">no</option>
			<option value="yes">yes</option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='backup_radcheck' class='form'>radcheck</label> 
		<select class='form' name="radcheck">
			<option value="<?php echo $radcheckTable ?>"><?php echo $radcheckTable ?></option>
			<option value="">  </option>
			<option value="no">no</option>
			<option value="yes">yes</option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='backup_radreply' class='form'>radreply</label> 
		<select class='form' name="radpostauth">
			<option value="<?php echo $radpostauth ?>"><?php echo $radpostauth ?></option>
			<option value="">  </option>
			<option value="no">no</option>
			<option value="yes">yes</option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='backup_radgroupcheck' class='form'>radgroupcheck</label> 
		<select class='form' name="radgroupcheck">
			<option value="<?php echo $radgroupcheckTable ?>"><?php echo $radgroupcheckTable ?></option>
			<option value="">  </option>
			<option value="no">no</option>
			<option value="yes">yes</option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='backup_radgroupreply' class='form'>radgroupreply</label> 
		<select class='form' name="radgroupreply">
			<option value="<?php echo $radgroupreplyTable ?>"><?php echo $radgroupreplyTable ?></option>
			<option value="">  </option>
			<option value="no">no</option>
			<option value="yes">yes</option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='backup_radusergroup' class='form'>radusergroup</label> 
		<select class='form' name="radusergroup">
			<option value="<?php echo $radusergroupTable ?>"><?php echo $radusergroupTable ?></option>
			<option value="">  </option>
			<option value="no">no</option>
			<option value="yes">yes</option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='backup_radpostauth' class='form'>radpostauth</label> 
		<select class='form' name="radpostauth">
			<option value="<?php echo $radpostauthTable ?>"><?php echo $radpostauthTable ?></option>
			<option value="">  </option>
			<option value="no">no</option>
			<option value="yes">yes</option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='backup_ippool' class='form'>ippool</label> 
		<select class='form' name="ippool">
			<option value="<?php echo $ippoolTable ?>"><?php echo $ippoolTable ?></option>
			<option value="">  </option>
			<option value="no">no</option>
			<option value="yes">yes</option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='backup_nas' class='form'>nas</label> 
		<select class='form' name="nas">
			<option value="<?php echo $nasTable ?>"><?php echo $nasTable ?></option>
			<option value="">  </option>
			<option value="no">no</option>
			<option value="yes">yes</option>
		</select>
		</li>

                <li class='fieldset'>
                <br/>
                <hr><br/>
                <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' class='button' />
                </li>

		</ul>
	</fieldset>

	</div>

     <div class="tabbertab" title="<?php echo $l['title']['daloRADIUSTables']; ?>">
        <br/>

        <fieldset>

                <h302> <?php echo $l['title']['Backups']; ?> </h302>
		<br/>

                <label class='form'>Select databases tables to backup:</label>

                <ul>


                <li class='fieldset'>
                <label for='backup_dalooperators' class='form'>operators</label> 
		<select class='form' name="operators">
			<option value="<?php echo $dalooperatorsTable ?>"><?php echo $dalooperatorsTable ?></option>
			<option value="">  </option>
			<option value="no">no</option>
			<option value="yes">yes</option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='backup_dalohotspots' class='form'>ohotspots</label> 
		<select class='form' name="hotspots">
			<option value="<?php echo $dalohotspotsTable ?>"><?php echo $dalohotspotsTable ?></option>
			<option value="">  </option>
			<option value="no">no</option>
			<option value="yes">yes</option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='backup_daloproxys' class='form'>proxys</label> 
		<select class='form' name="proxys">
			<option value="<?php echo $daloproxysTable ?>"><?php echo $daloproxysTable ?></option>
			<option value="">  </option>
			<option value="no">no</option>
			<option value="yes">yes</option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='backup_dalorealms' class='form'>realms</label> 
		<select class='form' name="realms">
			<option value="<?php echo $dalorealmsTable ?>"><?php echo $dalorealmsTable ?></option>
			<option value="">  </option>
			<option value="no">no</option>
			<option value="yes">yes</option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='backup_dalorates' class='form'>rates</label> 
		<select class='form' name="rates">
			<option value="<?php echo $daloratesTable ?>"><?php echo $daloratesTable ?></option>
			<option value="">  </option>
			<option value="no">no</option>
			<option value="yes">yes</option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='backup_dalouserinfo' class='form'>userinfo</label> 
		<select class='form' name="userinfo">
			<option value="<?php echo $dalouserinfoTable ?>"><?php echo $dalouserinfoTable ?></option>
			<option value="">  </option>
			<option value="no">no</option>
			<option value="yes">yes</option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='backup_dictionary' class='form'>dictionary</label> 
		<select class='form' name="dictionary">
			<option value="<?php echo $dictionaryTable ?>"><?php echo $dictionaryTable ?></option>
			<option value="">  </option>
			<option value="no">no</option>
			<option value="yes">yes</option>
		</select>
		</li>

                <li class='fieldset'>
                <br/>
                <hr><br/>
                <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' class='button' />
                </li>

                </ul>

        </fieldset>

	</div>
</div>


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
