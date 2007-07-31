<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "id";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";

	
	if (isset($_REQUEST['username']))
		$username = $_REQUEST['username'];

	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for user [$username] on page: ";
    include('include/config/logging.php');

?>

<?php

    include ("menu-reports.php");

?>

		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><?php echo $l[Intro][repusername.php]; ?></a></h2>
				
				<p>
				<?php echo $l[captions][recordsforuser]." ".$username ?> <br/>
				</p>



<?php

        
        include 'library/opendb.php';

	// table to display the radcheck information per the $username

        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='$username'  ORDER BY $orderBy $orderType;";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>".$l[captions][radcheckrecords]."</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> ".$l[all][ID]."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=id&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=id&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l[all][Username]."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=username&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=username&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l[all][Attribute]."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=attribute&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=attribute&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l[all][Value]."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=value&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=value&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l[all][Action]." </th>
                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[id] </td>
                        <td> $nt[UserName] </td>
                        <td> $nt[Attribute] </td>
                        <td> $nt[Value] </td>
                        <td> <a href='mng-edit.php?username=$nt[UserName]'> ".$l[all][edit]." </a> 
	                     <a href='mng-del.php?username=$nt[UserName]'> ".$l[all][del]." </a>
			     </td>
                </tr>";
        }
        echo "</table>";
	echo "<br/><br/>";


	
	
	// table to display the radreply information per the $username
        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADREPLY']." WHERE UserName='$username'  ORDER BY $orderBy $orderType;";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>".$l[captions][radreplyrecords]."</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>                        
                        <th scope='col'> ".$l[all][ID]."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=id&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=id&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l[all][Username]."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=username&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=username&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l[all][Attribute]."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=attribute&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=attribute&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l[all][Value]."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=value&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=value&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l[all][Action]." </th>                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[id] </td>
                        <td> $nt[UserName] </td>
                        <td> $nt[Attribute] </td>
                        <td> $nt[Value] </td>
                        <td> <a href='mng-edit.php?username=$nt[UserName]'> ".$l[all][edit]." </a> 
	                     <a href='mng-del.php?username=$nt[UserName]'> ".$l[all][del]." </a>
			     </td>
                </tr>";
        }
        echo "</table>";



        mysql_free_result($res);








        include 'library/closedb.php';
?>




				
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
