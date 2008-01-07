<?php

/*
 * populate_groups 
 * creates a select box and populates it with all groups from radgroupreply and
 * radgroupcheck
 * 
 * $defaultOption - title for the first/default option in select box
 * $elementName   - the string used for the select element's name='' value
 *
 */
function populate_groups($defaultOption = "Select Group",$elementName = "groupname") {

	echo "<select onChange=\"javascript:setStringText(this.id,'group')\" id='usergroup' 
			name='$elementName' tabindex=105>
			<option value=''>$defaultOption</option>";

        include 'library/opendb.php';

        // Grabing the group lists from usergroup table

        $sql = "(SELECT distinct(GroupName) FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY'].")
UNION (SELECT distinct(GroupName) FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK'].");";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                echo "  
                        <option value='$row[0]'> $row[0] </option>
                        ";

        }

        include 'library/closedb.php';

	echo "</select>";
}

?>

