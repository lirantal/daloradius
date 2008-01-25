<?php
/*********************************************************************
* Name: groups.php
* Author: Liran tal <liran.tal@gmail.com>
* 
* This file extends user management pages (specifically edit user
* page) to allow group management.
* Essentially, this extention populates groups into tables
*
*********************************************************************/

	// Grabing the group lists from usergroup table
	$sql = "(SELECT distinct(GroupName) FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY'].") UNION (SELECT distinct(GroupName) FROM 
".$configValues['CONFIG_DB_TBL_RADGROUPCHECK'].");";
	$res = $dbSocket->query($sql);

	$groupOptions = "";

	while($row = $res->fetchRow()) {			
		$groupOptions .= "<option value='$row[0]'> $row[0] </option>";
	}

?>

	<fieldset>

                <h302> Groups Assignment </h302>
		<br/>

<?php

	$sql = "SELECT GroupName, priority FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." 
WHERE UserName='".$dbSocket->escapeSimple($username)."';";
	$res = $dbSocket->query($sql);

	if ($res->numRows() == 0) {
		echo "
			<br/><center> ".$l['messages']['nogroupdefinedforuser']." <br/>".
					str_replace("create", "<a href='mng-rad-usergroup-new.php?username=$username'>create</a>", 
						$l['messages']['wouldyouliketocreategroup'])."

			</center><br/>";
	} else {

		$counter = 0;

		while($row = $res->fetchRow()) {

			echo "

				<input type='hidden' value='$row[0]' name='oldgroups[]' >

				<label for='group' class='form'>".$l['all']['Group']." #".($counter+1)."
				</label>
				<input value='$row[0]' name='groups[]' id='group$counter' >

				<select onChange=\"javascript:setStringText(this.id,'group$counter')\" 
					id='usergroup$counter' tabindex=105 class='form' >

				".$groupOptions."

				</select>

				<label for='groupPriority' class='form'>".$l['all']['GroupPriority']."
				</label>
				<input value='$row[1]' name='groups_priority[]' >
				<br/>
			";

			$counter++;

		} //while

	} // if-else
?>

	</fieldset>
	<br/>


