
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
</head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<body>
<?php
    include_once ("lang/main.php");
?>

<div id="wrapper">
<div id="innerwrapper">

<?php
    $m_active = "Graphs";
    include_once ("include/menu/menu-items.php");
	include_once ("include/menu/graphs-subnav.php");
?>      

		
		<div id="sidebar">
		
				<h2>Graphs</h2>
				
				<h3>Users plotting</h3>
				<ul class="subnav">

						<li><a href="javascript:document.overall_logins.submit();"><b>&raquo;</b>Overall Logins/Hits</a>
							<form name="overall_logins" action="graphs-overall_logins.php" method="post" class="sidebar">
							<input name="username" type="text" value="username">
							<select name="type" type="text">
								<option value="daily"> Daily
								<option value="monthly"> Monthly
								<option value="yearly"> Yearly
							</select>
							</form></li>






                                                <li><a href="javascript:document.overall_download.submit();"><b>&raquo;</b>Overall Download Stat</a>
                                                        <form name="overall_download" action="graphs-overall_download.php" method="post" class="sidebar">
                                                        <input name="username" type="text" value="username">
                                                        <select name="type" type="text">
                                                                <option value="daily"> Daily
                                                                <option value="monthly"> Monthly
                                                                <option value="yearly"> Yearly
                                                        </select>
                                                        </form></li>



                                                <li><a href="javascript:document.overall_upload.submit();"><b>&raquo;</b>Overall Upload Stat</a>
                                                        <form name="overall_upload" action="graphs-overall_upload.php" method="post" class="sidebar">
                                                        <input name="username" type="text" value="username">
                                                        <select name="type" type="text">
                                                                <option value="daily"> Daily
                                                                <option value="monthly"> Monthly
                                                                <option value="yearly"> Yearly
                                                        </select>
                                                        </form></li>

				<h3>All-time Statistics</h3>
				<ul class="subnav">


                                                <li><a href="javascript:document.alltime_logins.submit();"><b>&raquo;</b>Alltime Logins/Hits Stat</a>
                                                        <form name="alltime_logins" action="graphs-alltime_logins.php" method="post" class="sidebar">
                                                        <select name="type" type="text">
                                                                <option value="daily"> Daily
                                                                <option value="monthly"> Monthly
                                                                <option value="yearly"> Yearly
                                                        </select>
                                                        </form></li>



                                                <li><a href="javascript:document.alltime_traffic_compare.submit();"><b>&raquo;</b>Alltime Traffic comparison</a>
                                                        <form name="alltime_traffic_compare" action="graphs-alltime_traffic_compare.php" method="post" class="sidebar">
                                                        <select name="type" type="text">
                                                                <option value="daily"> Daily
                                                                <option value="monthly"> Monthly
                                                                <option value="yearly"> Yearly
                                                        </select>
                                                        </form></li>





				</ul>


				<br/><br/>
				<h2>Search</h2>
				<input name="" type="text" value="Search" />
				
		
		</div>
		
		
