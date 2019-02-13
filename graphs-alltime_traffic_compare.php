<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');



    $type = $_REQUEST['type'];
	$size = $_REQUEST['size'];

	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query of type [$type] on page: ";


?>

<?php	
	include ("menu-graphs.php");	
?>

<?php
        include_once ("library/tabber/tab-layout.php");
?>


		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','graphsalltimetrafficcompare.php'); ?>
		<h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','graphsalltimetrafficcompare') ?>
			<br/>
		</div>
		<br/>

<div class="tabber">

     <div class="tabbertab" title="Download Graph">
        <br/>
<?php		
        echo "<center>";
        echo "<img src=\"library/graphs-alltime-traffic-download.php?type=$type&size=$size\" />";
		echo "</center>";
?>
	</div>
     <div class="tabbertab" title="Upload Graph">
        <br/>

<?php
		echo "<center>";
        echo "<img src=\"library/graphs-alltime-traffic-upload.php?type=$type&size=$size\" />";
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
