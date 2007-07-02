<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

        $username = $_POST['username'];
        $type = $_POST['type'];


?>

<?php
	
	include ("menu-graphs.php");
	
?>		
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l[Intro][graphsoverallupload.php]; ?></a></h2>

<?php
        echo "<img src=\"library/exten-alltime_traffic_user_upload.php?type=$type&user=$username\" />";
	
	include 'library/graph-alltime_upload.php';
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
