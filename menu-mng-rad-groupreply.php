
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

                                <h3>Group Reply Management</h3>
                                <ul class="subnav">

                                                <li><a href="mng-rad-groupreply-list.php"><b>&raquo;</b>List Group Reply Mappings</a></li>
                                                <li><a href="mng-rad-groupreply-new.php"><b>&raquo;</b>New Group Reply Mapping</a></li>
                                                <li><a href="javascript:document.mngradgrprplyedit.submit();""><b>&raquo;</b>Edit Group Reply Mapping<a>
                                                        <form name="mngradgrprplyedit" action="mng-rad-groupreply-edit.php" method="get" class="sidebar">
                                                        <input name="groupname" type="text" value="[groupname]">
                                                        <input name="value" type="text" value="[value]">
                                                        </form></li>


                                                <li><a href="mng-rad-groupreply-del.php"><b>&raquo;</b>Remove Groupl Reply Mapping</a></li>
                                </ul>


	
		</div>

		