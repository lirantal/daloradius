<select onChange="javascript:setStringText(this.id,'group')" id='usergroup' tabindex=105>
        <option value=''>Select Group</option>
<?php   

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
?>
</select>

