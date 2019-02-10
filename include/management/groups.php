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
		$groupOptions .= "<option value='$row[0]'> $row[0] </option>";
	}

?>

	<fieldset>

                <h302> <?php echo $groupTerminology ?> Assignment </h302>
		<br/>

	        <h301> Associated <?php echo $groupTerminology ?>s </h301>
	        <br/>

		<ul>

<?php

	$sql = "SELECT GroupName, priority FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']
		." WHERE UserName='".$dbSocket->escapeSimple($username)."';";
	$res = $dbSocket->query($sql);

	if ($res->numRows() == 0) {
		echo "<center> ".t('messages','nogroupdefinedforuser')." <br/></center>";
	} else {

		$counter = 0;

		while($row = $res->fetchRow()) {

			echo "

				<li class='fieldset'>
				<label for='group' class='form'>".t('all')[$groupTerminology]." #".($counter+1)."</label>
				<select name='groups[]' id='usergroup$counter' tabindex=105 class='form' >
					<option value='$row[0]'>$row[0]</option>
					<option value=''></option>
					".$groupOptions."
				</select>

				<br/>
				<label for='groupPriority' class='form'>".t('all')[$groupTerminologyPriority]."</label>
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


