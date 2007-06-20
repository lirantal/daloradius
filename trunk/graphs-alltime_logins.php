<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

        //$username = $_POST['username'];
	$username = !empty($_REQUEST['username']) ? $_REQUEST['username'] : '';
        $type = $_POST['type'];


?>

<?php
	
	include ("menu-graphs.php");
	
?>
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Overall Usage</a></h2>

<?php

        echo "<img src=\"library/exten-alltime_logins.php?type=$type\" />";

	include 'library/graph-alltime_logins.php';
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
