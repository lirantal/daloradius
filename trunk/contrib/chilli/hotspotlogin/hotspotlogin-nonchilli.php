<?php
# daloRADIUS edition - fixed up variable definition through-out the code
# as well as parted the code for the sake of modularity and ability to 
# to support templates and languages easier.
# Copyright (C) Enginx and Liran Tal 2007, 2008

echo "
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
<html>
<head>
	<title>$title</title>
	<meta http-equiv=\"Cache-control\" content=\"no-cache\">
	<meta http-equiv=\"Pragma\" content=\"no-cache\">
</head>

<body bgColor = '#c0d8f4'>
	<h1 style=\"text-align: center;\">$h1Failed</h1>
	<center>
		$centerdaemon
	</center>
</body>
</html>
";

?>
