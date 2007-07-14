
<body>
<?php
    include_once ("lang/main.php");
?>
<div id="wrapper">
<div id="innerwrapper">

<?php
    $m_active = "Management";
    include_once ("include/menu/menu-items.php");
	include_once ("include/menu/management-subnav.php");
?>

		<div id="sidebar">
		
	
                                <h2>Management</h2>

                                <h3>Group Check Management</h3>
                                <ul class="subnav">

                                                <li><a href="mng-rad-groupcheck-list.php"><b>&raquo;</b>List Group Check Mappings</a></li>
                                                <li><a href="mng-rad-groupcheck-new.php"><b>&raquo;</b>New Group Check Mapping</a></li>
                                                <li><a href="javascript:document.mngradgrpchkedit.submit();""><b>&raquo;</b>Edit Group Check Mapping<a>
                                                        <form name="mngradgrpchkedit" action="mng-rad-groupcheck-edit.php" method="get" class="sidebar">
                                                        <input name="groupname" type="text" value="[groupname]">
                                                        <input name="value" type="text" value="[value]">
                                                        </form></li>


                                                <li><a href="mng-rad-groupcheck-del.php"><b>&raquo;</b>Remove Group Check Mapping</a></li>
                                </ul>


	
		</div>

		