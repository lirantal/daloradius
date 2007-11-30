<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');



	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    include('include/config/logging.php');

?>

<?php

    include ("menu-reports-logs.php");
  	
?>	
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"  onclick="javascript:toggleShowDiv('helpPage')"><? echo $l['Intro']['replogssystem.php']; ?>
		<h144>+</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo $l['helpPage']['replogssystem'] ?>
			<br/>
		</div>
		<br/>

<?php
	include 'library/exten-syslog_log.php';
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
