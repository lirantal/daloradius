<?php

/*
 * populate_groups 
 * creates a select box and populates it with all groups from radgroupreply and
 * radgroupcheck
 * 
 * $defaultOption - title for the first/default option in select box
 * $elementName   - the string used for the select element's name='' value
 * $cssClass	  - the css/xhtml class name, default is form for displaying on content divs (not sidebar)
 *
 */
function populate_groups($defaultOption = "Select Group",$elementName = "", $cssClass = "form", $mode = "") {

	echo "<select onChange=\"javascript:setStringText(this.id,'group')\" id='usergroup' $mode
			name='$elementName' class='$cssClass' tabindex=105 />
			<option value=''>$defaultOption</option>";

        include 'library/opendb.php';

        // Grabing the group lists from usergroup table

        $sql = "(SELECT distinct(GroupName) FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY'].")".
			"UNION (SELECT distinct(GroupName) FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK'].");";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                echo "  
                        <option value='$row[0]'> $row[0] </option>
                        ";

        }

        include 'library/closedb.php';

	echo "</select>";
}





/*
 * populate_vendors()
 *
 * the populate vendors function returns all the vendors found in the dictionary table in an ascending 
 * alphabetical order
 */
function populate_vendors($defaultOption = "Select Vendor",$elementName = "", $cssClass = "form", $mode = "") {

	echo "<select onChange=\"javascript:setStringText(this.id,'group')\" id='usergroup' $mode
			name='$elementName' class='$cssClass' tabindex=105
			<option value=''>$defaultOption</option>";

        include 'library/opendb.php';

        // Grabing the group lists from usergroup table

	$configValues['CONFIG_DB_TBL_DICTIONARY'] = "dictionary";

        $sql = "SELECT distinct(Vendor) as Vendor FROM ".$configValues['CONFIG_DB_TBL_DICTIONARY']." ORDER BY Vendor ASC;";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                echo "  
                        <option value='$row[0]'> $row[0] </option>
                        ";

        }

	echo "</select>";

        include 'library/closedb.php';
}





/*
 * populate_realms()
 *
 * the populate realms function returns all the realms found in the realms table in ascending
 * alphabetical order
 */
function populate_realms($defaultOption = "Select Realm",$elementName = "", $cssClass = "form", $mode = "") {

	echo "<select onChange=\"javascript:setStringText(this.id,'realm')\" id='realmlist' $mode
			name='$elementName' class='$cssClass' tabindex=105
			<option value=''>$defaultOption</option>";

        include 'library/opendb.php';

        // Grabing the group lists from usergroup table

	$configValues['CONFIG_DB_TBL_DALOREALMS'] = "realms";

        $sql = "SELECT distinct(RealmName) as Realm FROM ".$configValues['CONFIG_DB_TBL_DALOREALMS']." ORDER BY Realm ASC;";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                echo "  
                        <option value='$row[0]'> $row[0] </option>
                        ";

        }

	echo "</select>";

        include 'library/closedb.php';

}







/*
 * populate_proxys()
 *
 * the populate realms function returns all the realms found in the realms table in ascending
 * alphabetical order
 */
function populate_proxys($defaultOption = "Select Proxy",$elementName = "", $cssClass = "form", $mode = "") {

	echo "<select onChange=\"javascript:setStringText(this.id,'proxy')\" id='proxylist' $mode
			name='$elementName' class='$cssClass' tabindex=105
			<option value=''>$defaultOption</option>";

        include 'library/opendb.php';

        // Grabing the group lists from usergroup table

	$configValues['CONFIG_DB_TBL_DALOPROXYS'] = "proxys";

        $sql = "SELECT distinct(ProxyName) as Proxy FROM ".$configValues['CONFIG_DB_TBL_DALOPROXYS']." ORDER BY Proxy ASC;";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                echo "  
                        <option value='$row[0]'> $row[0] </option>
                        ";

        }

	echo "</select>";

        include 'library/closedb.php';

}








/*
 * drawTables()
 *
 * an aid function to return the possible options for tables (check or reply)
 */
function drawTables() {

	echo "
		<option value='check'>check</option>
		<option value='reply'>reply</option>
	";
}






/*
 * drawOptions()
 *
 * an aid function to return the possible options for op (operator) values
 * for attributes
 */
function drawOptions() {

	echo "
                <option value='='>=</option>
                <option value=':='>:=</option>
                <option value='=='>==</option>
                <option value='+='>+=</option>
                <option value='!='>!=</option>
                <option value='>'>></option>
                <option value='>='>>=</option>
                <option value='<'><</option>
                <option value='<='><=</option>
                <option value='=~'>=~</option>
                <option value='!~'>!~</option>
                <option value='=*'>=*</option>
                <option value='!*'>!*</option>

        ";
}





/*
 * drawTypes()
 *
 * an aid function to return the possible attribute types for
 * a given attribute
 */
function drawTypes() {

	echo "
                <option value='string'>string</option>
                <option value='integer'>integer</option>
                <option value='ipaddr'>ipaddr</option>
                <option value='date'>date</option>
                <option value='octets'>octets</option>
                <option value='ipv6addr'>ipv6addr</option>
                <option value='ifid'>ifid</option>
                <option value='abinary'>abinary</option>
        ";
}


/*
 * drawRecommendedHelpers()
 *
 * an aid function to return the possible helper functions for
 * different attributes
 */
function drawRecommendedHelper() {

	echo "
                <option value='date'>date</option>
                <option value='datetime'>datetime</option>
                <option value='authtype'>authtype</option>
                <option value='framedprotocol'>framedprotocol</option>
                <option value='servicetype'>servicetype</option>
                <option value='bitspersecond'>bitspersecond</option>
                <option value='volumebytes'>volumebytes</option>
        ";
}





?>
