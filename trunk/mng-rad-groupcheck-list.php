<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
 
<?php
	include ("menu-mng-rad-groupcheck.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#"><?php echo $l[Intro][mngradgroupchecklist.php] ?></a></h2>
				
				<p>

<?php

        
        include 'library/opendb.php';


        $sql = "select GroupName, Attribute, op, Value FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK']." ORDER BY GroupName ASC";
        $res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>".$l[all][Records]."</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> ".$l[all][Groupname]." </th>
                        <th scope='col'> ".$l[all][Attribute]." </th>
                        <th scope='col'> ".$l[all][Operator]." </th>						
                        <th scope='col'> ".$l[all][Value]." </th>
                        <th scope='col'> ".$l[all][Action]." </th>						
                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[GroupName] </td>
                        <td> $nt[Attribute] </td>
                        <td> $nt[op] </td>						
                        <td> $nt[Value] </td>						
                        <td> <a href='mng-rad-groupcheck-edit.php?groupname=$nt[GroupName]&value=$nt[Value]'> ".$l[all][edit]." </a>
                             <a href='mng-rad-groupcheck-del.php?groupname=$nt[GroupName]&value=$nt[Value]'> ".$l[all][del]." </a>
                             </td>

                </tr>";
        }
        echo "</table>";

        mysql_free_result($res);
        include 'library/closedb.php';
?>


				</p>
				
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
