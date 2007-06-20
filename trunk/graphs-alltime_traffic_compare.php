<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

        $type = $_POST['type'];


?>

<?php
	
	include ("menu-graphs.php");
	
?>
		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Overall Usage</a></h2>

	<?php echo "<img src=\"library/exten-alltime_traffic_compare.php?type=$type\" "; ?><br/><br/><br/>
	<?php echo "<img src=\"library/exten-alltime_traffic_stat_upload.php?type=$type\" "; ?><br/><br/><br/>
	<?php echo "<img src=\"library/exten-alltime_traffic_stat_download.php?type=$type\" "; ?><br/><br/><br/>

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
