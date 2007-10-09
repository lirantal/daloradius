<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
	
	include_once('library/config_read.php');
    $log = "visited page: ";

?>

<?php
	
	include("menu-accounting.php");
	
?>
		
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l['Intro']['acctmain.php'];?></a></h2>
				
				<p>

<?php
	include('include/config/logging.php');
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
