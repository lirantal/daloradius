<?php

	$username = $_POST['username'];

?>

<?php

    include ("menu-reports.php");

?>
		
		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Users Listing</a></h2>
				
				<p>
				Records found for user: <?php echo $username ?> <br/>
				</p>



<?php

        include 'library/config.php';
        include 'library/opendb.php';

	// table to display the radcheck information per the $username

        $sql = "SELECT * FROM radcheck WHERE UserName='$username'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>RADIUS radcheck Records</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> Account ID </th>
                        <th scope='col'> Username </th>
                        <th scope='col'> Attribute </th>
                        <th scope='col'> Value </th>
                        <th scope='col'> Action </th>
                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[id] </td>
                        <td> $nt[UserName] </td>
                        <td> $nt[Attribute] </td>
                        <td> $nt[Value] </td>
                        <td> <a href='mng-edit.php?username=$nt[UserName]'> edit </a> 
	                     <a href='mng-del.php?username=$nt[UserName]'> del </a>
			     </td>
                </tr>";
        }
        echo "</table>";
	echo "<br/><br/>";


	
	
	// table to display the radreply information per the $username
        $sql = "SELECT * FROM radreply WHERE UserName='$username'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>RADIUS radreply Records</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> Account ID </th>
                        <th scope='col'> Username </th>
                        <th scope='col'> Attribute </th>
                        <th scope='col'> Value </th>
                        <th scope='col'> Action </th>
                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[id] </td>
                        <td> $nt[UserName] </td>
                        <td> $nt[Attribute] </td>
                        <td> $nt[Value] </td>
                        <td> <a href='mng-edit.php?username=$nt[UserName]'> edit </a> 
	                     <a href='mng-del.php?username=$nt[UserName]'> del </a>
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
