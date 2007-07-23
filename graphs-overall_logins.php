<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');
	
        $username = $_POST['username'];
        $type = $_POST['type'];


?>

<?php
	
	include ("menu-graphs.php");
	
?>
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l[Intro][graphsoveralllogins.php]; ?></a></h2>

<?php
	echo "<center>";
	echo "<img src=\"library/graphs-overall-users-login.php?type=$type&user=$username\" />";
	echo "</center>";
	include 'library/tables-overall-users-login.php';
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
