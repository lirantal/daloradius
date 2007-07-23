<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');
	
        $type = $_POST['type'];


?>

<?php
	
	include ("menu-graphs.php");
	
?>
		
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l[Intro][graphsalltimetrafficcompare.php]; ?></a></h2>

<?php		
        echo "<br/><br/>";
        echo "<center>";
        echo "<img src=\"library/graphs-alltime-traffic-download.php?type=$type\" />";
        echo "<img src=\"library/graphs-alltime-traffic-upload.php?type=$type\" />";
        echo "</center>";
		
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
