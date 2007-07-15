<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	$username = "";
	$username = $_REQUEST['username'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
 
<?php
	include ("menu-mng-rad-usergroup.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">User-Group Mapping in Database</a></h2>
				
				<p>

<?php

        
        include 'library/opendb.php';


        $sql = "SELECT * FROM usergroup WHERE UserName='$username'";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>Records</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> Username </th>
                        <th scope='col'> Group </th>
                        <th scope='col'> Priority </th>
                        <th scope='col'> Action </th>
                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[UserName] </td>
                        <td> $nt[GroupName] </td>
                        <td> $nt[priority] </td>
                        <td> <a href='mng-rad-usergroup-edit.php?username=$nt[UserName]&group=$nt[GroupName]'> edit </a>
                             <a href='mng-rad-usergroup-del.php?username=$nt[UserName]&group=$nt[GroupName]'> del </a>
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
