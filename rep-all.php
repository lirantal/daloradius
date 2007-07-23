<?php

    include ("menu-reports.php");

?>

		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l[Intro][repall.php]; ?></a></h2>
				
				<p>
				<?echo $l[captions][listingusersindb]; ?><br/>
				</p>



<?php

        
        include 'library/opendb.php';


	/* we are searching for both kind of attributes for the password, being User-Password, the more
	   common one and the other which is Password, this is also done for considerations of backwards
	   compatibility with version 0.7        */
	
        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE (Attribute='User-Password' or Attribute='Password')";
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
                        <th scope='col'> ".$l[all][ID]." </th>
                        <th scope='col'> ".$l[all][Username]." </th>
                        <th scope='col'> ".$l[all][Password]." </th>
                        <th scope='col'> ".$l[all][Action]." </th>
                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[id] </td>
                        <td> $nt[UserName] </td>
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
