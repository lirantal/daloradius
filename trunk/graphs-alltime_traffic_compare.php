<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');



    $type = $_REQUEST['type'];

	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query of type [$type] on page: ";


?>

<?php
        include_once ("library/tabber/tab-layout.php");
?>

<?php
	
	include ("menu-graphs.php");
	
?>
		
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l['Intro']['graphsalltimetrafficcompare.php']; ?></a></h2>

<div class="tabber">

     <div class="tabbertab" title="Download Graph">
        <br/>
<?php		
        echo "<center>";
        echo "<img src=\"library/graphs-alltime-traffic-download.php?type=$type\" />";
?>
	</div>
     <div class="tabbertab" title="Upload Graph">
        <br/>

<?php
        echo "<img src=\"library/graphs-alltime-traffic-upload.php?type=$type\" />";
        echo "</center>";		
?>
	</div>
</div>
	

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
