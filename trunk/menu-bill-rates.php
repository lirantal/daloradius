<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->

<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>

<body>
<?php
    include_once ("lang/main.php");
?>
<div id="wrapper">
<div id="innerwrapper">

<?php
    $m_active = "Billing";
    include_once ("include/menu/menu-items.php");
	include_once ("include/menu/billing-subnav.php");
?>

<div id="sidebar">

                                <h2>Billing</h2>

                                <h3>Track Rates</h3>
	<ul class="subnav">

                <li><a href="javascript:document.acctdate.submit();"><b>&raquo;</b><?php echo $l['button']['DateAccounting'] ?></a>
                        <form name="acctdate" action="acct-date.php" method="get" class="sidebar">
                        <input name="username" type="text"
                                value="<?php if (isset($accounting_date_username)) echo $accounting_date_username;
                                else echo 'username'; ?>">
                        <input name="startdate" type="text" id="startdate"
                                value="<?php if (isset($accounting_date_startdate)) echo $accounting_date_startdate;
                        else echo date("Y-m-d"); ?>">

                        <img src="library/js_date/calendar.gif"
                                onclick="showChooser(this, 'startdate', 'chooserSpan', 1950, 2010, 'Y-m-d', false);">
                        <div id="chooserSpan" class="dateChooser select-free"
                                style="display: none; visibility: hidden;       width: 160px;"></div>

                        <input name="enddate" type="text" id="enddate"
                                value="<?php if (isset($accounting_date_enddate)) echo $accounting_date_enddate;
                                else echo date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")+1,
                                date("Y"))); ?>">

                        <img src="library/js_date/calendar.gif"
                                onclick="showChooser(this, 'enddate', 'chooserSpan', 1950, 2010, 'Y-m-d', false);">
                        <div id="chooserSpan" class="dateChooser select-free"
                                style="display: none; visibility: hidden; width: 160px;"></div>

                        </form></li>

		</ul>

                                <h3>Rates Management</h3>
                                <ul class="subnav">

                                                <li><a href="bill-rates-list.php"><b>&raquo;</b><?php echo $l['button']['ListRates'] ?></a></li>
                                                <li><a href="bill-rates-new.php"><b>&raquo;</b><?php echo $l['button']['NewRate'] ?></a></li>
                                                <li><a href="javascript:document.billratesedit.submit();""><b>&raquo;</b><?php echo $l['button']['EditRate'] ?></a>
                                                        <form name="billratesedit" action="bill-rates-edit.php" method="get" class="sidebar">
                                                        <input name="ratename" type="text" id="ratename" 
								value="<?php if (isset($edit_rateName)) echo $edit_rateName; ?>" tabindex=3>
                                                        </form></li>
                                                <li><a href="bill-rates-del.php"><b>&raquo;</b><?php echo $l['button']['RemoveRate'] ?></a></li>
                                </ul>

                                <br/><br/>
                                <h2>Search</h2>

			<input name="" type="text" value="Search" tabindex=4 />

                </div>

