<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "numberoflogins";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";

	isset($_REQUEST['type']) ? $type = $_REQUEST['type'] : $type = "daily";

	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query of type [$type] on page: ";
    include('include/config/logging.php');


?>

<?php
        include_once ("library/tabber/tab-layout.php");
?>

<?php
	
	include ("menu-graphs.php");
	
?>
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l[Intro][graphsalltimelogins.php]; ?></a></h2>

<div class="tabber">

     <div class="tabbertab" title="Graph">
        <br/>
<?php
        echo "<center>";
        echo "<img src=\"library/graphs-alltime-users-login.php?type=$type\" />";
        echo "</center>";
?>
	</div>
     <div class="tabbertab" title="Statistics">	
	<br/>
<?php
        include 'library/tables-alltime-users-login.php';
?>
	</div>
</div>
		

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
