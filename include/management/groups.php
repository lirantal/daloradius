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

	if (!isset($groupTerminology)) {
		$groupTerminology = "Group";
		$groupTerminologyPriority = "GroupPriority";
	}
		


	// Grabing the group lists from usergroup table
	$sql = "(SELECT distinct(GroupName) FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY'].
		") UNION (SELECT distinct(GroupName) FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK'].");";
	$res = $dbSocket->query($sql);

	$groupOptions = "";

	while($row = $res->fetchRow()) {			
		$groupOptions .= "<option value='" . htmlspecialchars($row[0], ENT_QUOTES) . "'> " . htmlspecialchars($row[0], ENT_QUOTES) . " </option>";
	}

?>

	<fieldset>

                <h302> <?php echo htmlspecialchars($groupTerminology, ENT_QUOTES) ?> Assignment </h302>
		<br/>

	        <h301> Associated <?php echo htmlspecialchars($groupTerminology, ENT_QUOTES) ?>s </h301>
	        <br/>

		<ul>

<?php

	$sql = "SELECT GroupName, priority FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']
		." WHERE UserName='".$dbSocket->escapeSimple($username)."';";
	$res = $dbSocket->query($sql);

	if ($res->numRows() == 0) {
		echo "<center> ".$l['messages']['nogroupdefinedforuser']." <br/></center>";
	} else {

		$counter = 0;

		while($row = $res->fetchRow()) {

			echo "

				<li class='fieldset'>
				<label for='group' class='form'>".$l['all'][$groupTerminology]." #".($counter+1)."</label>
				<select name='groups[]' id='usergroup$counter' tabindex=105 class='form' >
					<option value='" . htmlspecialchars($row[0], ENT_QUOTES) . "'>" . htmlspecialchars($row[0], ENT_QUOTES) . "</option>
					<option value=''></option>
					".$groupOptions."
				</select>

				<br/>
				<label for='groupPriority' class='form'>".$l['all'][$groupTerminologyPriority]."</label>
				<input class='integer' value='$row[1]' name='groups_priority[]' id='group_priority$counter' >
				<img src=\"images/icons/bullet_arrow_up.png\" alt=\"+\" 
					onclick=\"javascript:changeInteger('group_priority$counter','increment')\" />
				<img src=\"images/icons/bullet_arrow_down.png\" alt=\"-\" 
					onclick=\"javascript:changeInteger('group_priority$counter','decrement')\"/>

				<br/>
				</li>
			";

			$counter++;

		} //while

	} // if-else
?>


