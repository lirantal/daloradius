<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	
        $username = $_POST['username'];
        $type = $_POST['type'];



	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for user [$username] of type [$type] on page: ";
    include('include/config/logging.php');

?>

<?php
	
	include ("menu-graphs.php");
	
?>		
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l[Intro][graphsoverallupload.php]; ?></a></h2>

<?php
    echo "<br/>";
    echo "<center>";
    echo "<img src=\"library/graphs-overall-users-upload.php?type=$type&user=$username\" />";
    echo "</center>";
    include 'library/tables-overall-users-upload.php';
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
