<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

        
	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');
	
?>
		
<?php

    include ("menu-config.php");

?>
		
		
		<div id="contentnorightbar">
		
			<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['configmain.php'] ?>
			<h144>+</h144></a></h2>
                
				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['configmain'] ?>
					<br/>
				</div>
				<br/>

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
