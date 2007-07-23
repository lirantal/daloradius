<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
 
<?php
	include ("menu-mng-rad-nas.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#"><?php echo $l[Intro][mngradnaslist.php] ?></a></h2>
				
				<p>

<?php

        
        include 'library/opendb.php';


        $sql = "SELECT * FROM nas";
        $res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>Records</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> ".$l[all][NasID]." </th>
                        <th scope='col'> ".$l[all][NasIPHost]." </th>
                        <th scope='col'> ".$l[all][NasShortname]." </th>
                        <th scope='col'> ".$l[all][NasType]." </th>
                        <th scope='col'> ".$l[all][NasPorts]." </th>
                        <th scope='col'> ".$l[all][NasSecret]." </th>
                        <th scope='col'> ".$l[all][NasCommunity]." </th>
                        <th scope='col'> ".$l[all][NasDescription]." </th>
                        <th scope='col'> ".$l[all][Action]." </th>
                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[id] </td>
                        <td> $nt[nasname] </td>
                        <td> $nt[shortname] </td>
                        <td> $nt[type] </td>
                        <td> $nt[ports] </td>
                        <td> $nt[secret] </td>
                        <td> $nt[community] </td>
                        <td> $nt[description] </td>
                        <td> <a href='mng-rad-nas-edit.php?nashost=$nt[nasname]'> ".$l[all][edit]." </a>
                             <a href='mng-rad-nas-del.php?nashost=$nt[nasname]'> ".$l[all][del]." </a>
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
