<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
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

</head>
 
<?php

	include ("menu-help.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Help</a></h2>
				<p>
				
				One of several communication mediums available at your disposal:<br/><br/>
				
				<b>daloRADIUS website</b>: <a href="http://www.daloradius.com">daloRADIUS blog</a><br/><br/>
				<b>daloRADIUS Project at GitHub</b>: <a href="https://github.com/lirantal/daloradius">GitHub project</a><br/>
				At GitHub you may find the trackers for submitting tickets for support, bugs or features for next releases.<br/>
				The official daloRADIUS package is available at
				GitHub as well.<br/><br/>
				<b>daloRADIUS Project at SourceForge</b>: <a href="http://sourceforge.net/projects/daloradius/">SourceForge project</a><br/>
				Forums and the mailing list archive to review and search for issues.<br/>
				The daloRADIUS packages here are old, use the GitHub ones instead.<br/><br/>
				<b>daloRADIUS Mailing List</b>: Email to daloradius-users@lists.sourceforge.net, though registration to the mailing list<br/>
				is required first <a href="https://lists.sourceforge.net/lists/listinfo/daloradius-users">here</a><br/><br/>
				
				<b>daloRADIUS IRC</b>: you can find us at #daloradius on irc.freenode.net
				</p>
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
