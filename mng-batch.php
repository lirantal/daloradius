<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        

	$username_prefix = "";
	$number = "";
	$length_pass = "";
	$length_user = "";

function createPassword($length) {

    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;

    while ($i <= ($length - 1)) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }

    return $pass;

}


	if (isset($_POST['submit'])) {
		$username_prefix = $_REQUEST['username_prefix'];
		$number = $_REQUEST['number'];
		$length_pass = $_REQUEST['length_pass'];
		$length_user = $_REQUEST['length_user'];
		
		include 'library/opendb.php';
	    include 'include/management/attributes.php';                            // required for checking if an attribute

		$actionMsgBadUsernames = "";
		$actionMsgGoodUsernames = "";
		
		for ($i=0; $i<$number; $i++) {
			$username = createPassword($length_user);
			$password = createPassword($length_pass);

			// append the prefix to the username
			$username  = $username_prefix . $username;

//			echo "username: $username <br/>";
//			echo "password: $password <br/>";

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='$username'";
		$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

		if (mysql_num_rows($res) > 0) {
			$actionStatus = "failure";
			$actionMsgBadUsernames = $actionMsgBadUsernames . $username . ", " ;
			$actionMsg = "skipping matching entry: <b> $actionMsgBadUsernames </b>";
		} else {
		
			// insert username/password
			$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADCHECK']." values (0, '$username', 'User-Password', '==', '$password')";
			$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

				 foreach( $_POST as $attribute=>$value ) { 

					if ( ($attribute == "username_prefix") || ($attribute == "length_pass") || ($attribute == "length_user") || ($attribute == "number") || ($attribute == "submit") )	
						continue; // we skip these post variables as they are not important

					if (!($value[0]))
						continue;
						
						$useTable = checkTables($attribute);			// checking if the attribute's name belong to the radreply
																		// or radcheck table (using include/management/attributes.php function)

				        $counter = 0;

						$sql = "INSERT INTO $useTable values (0, '$username', '$attribute', '" . $value[1] ."', '$value[0]')  ";
                        $res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

						$counter++;

				} // foreach

				$actionMsgGoodUsernames = $actionMsgGoodUsernames . $username . ", " ;
				$actionStatus = "success";
				$actionMsg = "Added to database new user: <b> $actionMsgGoodUsernames </b>";
				$logAction = "Successfully added to database new users [$actionMsgGoodUsernames] on page: ";
		} 
		
		}

		include 'library/closedb.php';

	}




	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');



?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->

</head>

<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>

<?php

	include ("menu-mng-main.php");
	
?>

		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><?php echo $l[Intro][mngbatch.php] ?></h2>
				
				<p>
				<?php echo $l[captions][mngbatch] ?><br/>
				<br/><br/>

				</p>
				<form name="batchuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<table border='2' class='table1'>
<tr><td>
						<b><?php echo $l[FormField][mngbatch.php][UsernamePrefix] ?></b>
</td><td>
						<input value="<?php echo $username_prefix ?>" name="username_prefix"/><br/>
</td></tr>
<tr><td>
						<b><?php echo $l[FormField][mngbatch.php][NumberInstances] ?></b>
</td><td>
						<input value="<?php echo $number ?>" name="number" /><br/>
</td></tr>
<tr><td>

						<b><?php echo $l[FormField][mngbatch.php][UsernameLength] ?></b>
</td><td>
	<SELECT name="length_user">
          <OPTION id="4"> 4 </OPTION>
          <OPTION id="5"> 5 </OPTION>
          <OPTION id="6"> 6 </OPTION>
          <OPTION id="8"> 8 </OPTION>
          <OPTION id="10"> 10 </OPTION>
          <OPTION id="12"> 12 </OPTION>
        </SELECT><br/>
</td></tr>
<tr><td>

						<b><?php echo $l[FormField][mngbatch.php][PasswordLength] ?></b>
</td><td>
	<SELECT name="length_pass">
          <OPTION id="4"> 4 </OPTION>
          <OPTION id="5"> 5 </OPTION>
          <OPTION id="6"> 6 </OPTION>
          <OPTION id="8"> 8 </OPTION>
          <OPTION id="10"> 10 </OPTION>
          <OPTION id="12"> 12 </OPTION>
        </SELECT><br/>
</td></tr>
</table>

			<br/><br/>
<center>
			<input type="submit" name="submit" value="<?php echo $l[buttons][apply] ?> "/>
</center>
<br/><br/>

<?php
        include_once('include/management/attributes.php');
        drawAttributes();
?>
<br/>


				</form>
		
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





