<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');

    include ("library/config_read.php");

    if (isset($_REQUEST['submit'])) {

		if (isset($_REQUEST['config_lang']))
			$configValues['CONFIG_LANG'] = $_REQUEST['config_lang'];
			
            include ("library/config_write.php");
    }
	

	
?>		

<?php

    include ("menu-config.php");

?>		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['configlang.php'] ?>
				<h144>+</h144></a></h2>
                <div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['configlang'] ?>
					<br/>
				</div>
				<br/>

				<form name="langsettings" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

        <fieldset>

                <h302> <?php echo $l['title']['Settings']; ?> </h302>
                <br/>

                <ul>


                <li class='fieldset'>
                <label for='config_lan' class='form'><?php echo $l['all']['PrimaryLanguage']?></label>
		<select name="config_lang">
			<option value="en"> en </option>
			<option value="ru"> ru </option>
		</select>
		</li>

                <li class='fieldset'>
                <br/>
                <hr><br/>
                <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' class='button' />
                </li>

                </ul>

        </fieldset>

				</form>

	
				<br/><br/>
				

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
