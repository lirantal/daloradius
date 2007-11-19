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
?>

                <table border='2' class='table1'>

                        <thead>
                                <tr>
                                <th colspan='10'><?php echo $l['table']['Groups']; ?></th>
                                </tr>
                        </thead>

<tr><td>                                        <b><?php echo $l['FormField']['all']['Group']; ?></b>
</td><td>
                                                <input value="<?php if (isset($group)) echo $group ?>" name="group" id="group" tabindex=111 />

<select onChange="javascript:setStringText(this.id,'group')" id='usergroup' tabindex=105>
<?php

        include 'library/opendb.php';

        // Grabing the group lists from usergroup table

	$sql = "(SELECT distinct(GroupName) FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY'].") UNION (SELECT distinct(GroupName) FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK'].");";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                echo "
                        <option value='$row[0]'> $row[0]
                        ";

        }

        include 'library/closedb.php';
?>
</select>
</td></tr>
<tr><td>                                        <b><?php echo $l['FormField']['all']['GroupPriority']; ?></b>
</td><td>
                                                <input value="<?php if (isset($group_priority)) echo $group_priority ?>" name="group_priority" id="group_priority" tabindex=111 />
</td></tr>


                </table>
        <br/>




