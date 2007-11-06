<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];


	include_once('library/config_read.php');
    $log = "visited page: ";
	
?>
		
<?php

    include ("menu-config-maint.php");

?>		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['configmaint.php'] ?></a></h2>
                <div id="helpPage" style="display:none;visibility:visible" >
			<?php echo $l['helpPage']['configmaint'] ?>		
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
