<?php


/* getAjaxAutocompleteUsernames provides a trigger to this callback routine which returns the possible usernames in the
 * radcheck table matching the getAjaxAutocompleteUsernames variable's value wildcard.
 */
if(isset($_GET['getAjaxAutocompleteUsernames'])) {

        $getAjaxAutocompleteUsernames = $_GET['getAjaxAutocompleteUsernames'];

        include '../../library/opendb.php';

        $sql = "SELECT distinct(Username) as Username FROM ".$configValues['CONFIG_DB_TBL_RADCHECK'].
			" WHERE Username LIKE '$getAjaxAutocompleteUsernames%' ORDER BY Username ASC";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                echo "$row[0]###$row[0]|";
        }

        include '../../library/closedb.php';

}




/* getAjaxAutocompleteAttributes - if this GET variable is set then an sql query to the database is performed
 * to retrieve all the possible attributes which match the wildcard syntax for the getAjaxAutocompleteAttributes
 * variable's value, which is meant to produce an auto-complete possible values.
 *
 * This is working in accordance to the auto-complete javascript library.
 */
if(isset($_GET['getAjaxAutocompleteAttributes'])) {

        $getAjaxAutocompleteAttributes = $_GET['getAjaxAutocompleteAttributes'];

        include '../../library/opendb.php';

        $sql = "SELECT distinct(Attribute) as Attribute FROM dictionary WHERE Attribute LIKE '$getAjaxAutocompleteAttributes%' ".
                "ORDER BY Vendor ASC";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                echo "$row[0]###$row[0]|";
        }

        include '../../library/closedb.php';

}



?>
