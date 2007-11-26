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

                <table border='2' class='table1'>

                        <thead>
                                <tr>
                                <th colspan='10'><?php echo $l['table']['Groups']; ?></th>
                                </tr>
                        </thead>

<?php

	$sql = "SELECT GroupName, priority FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." 
WHERE UserName='".$dbSocket->escapeSimple($username)."';";
	$res = $dbSocket->query($sql);

	if ($res->numRows() == 0) {
		echo "</table> 
			<br/><center> ".$l['messages']['nogroupdefinedforuser']." <br/>".
					str_replace("create", "<a href='mng-rad-usergroup-new.php?username=$username'>create</a>", 
						$l['messages']['wouldyouliketocreategroup'])."

			</center><br/>";
	} else {

		$counter = 0;

		while($row = $res->fetchRow()) {

			echo "

				<input type='hidden' value='$row[0]' name='oldgroups[]' >

				<tr><td>        <b>".$l['FormField']['all']['Group']." #".($counter+1)."</b>
				</td><td>       <input value='$row[0]' name='groups[]' id='group$counter' >

				<select onChange=\"javascript:setStringText(this.id,'group$counter')\" id='usergroup$counter' tabindex=105>

				".$groupOptions."

				</select>

				</td></tr>
				<tr><td>        <b>". $l['FormField']['all']['GroupPriority']."</b>
				</td><td>       <input value='$row[1]' name='groups_priority[]' >
				</td></tr>

			";

			$counter++;

		} //while

	} // if-else
?>

	</table>
	<br/>


