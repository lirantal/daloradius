
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
	
	<h3>Attributes Management</h3>
	<ul class="subnav">
	
		<li><a href="javascript:document.mngradattributeslist.submit();"><b>&raquo;</b><?php echo $l['button']['ListAttributesforVendor'] ?>
			</a>
                        <form name="mngradattributeslist" action="mng-rad-attributes-list.php" method="get" class="sidebar">
                        <?php
                                include 'include/management/populate_selectbox.php';
                                populate_vendors("Select Vendor","vendor","");
                        ?>
			</form>
		</li>

		<li><a href="mng-rad-attributes-new.php" tabindex=2><b>&raquo;</b><?php echo $l['button']['NewVendorAttribute'] ?></a></li>
		<li><a href="javascript:document.mngradattributesedit.submit();" tabindex=3 ><b>&raquo;</b><?php echo $l['button']['EditVendorAttribute'] ?></a>
			<form name="mngradattributesedit" action="mng-rad-attributes-edit.php" method="get" class="sidebar">
			<input name="vendor" type="text" tabindex=4>
			<input name="attribute" type="text" tabindex=5>
			</form></li>
		<li><a href="mng-rad-attributes-del.php" tabindex=5><b>&raquo;</b><?php echo $l['button']['RemoveVendorAttribute'] ?></a></li>
		
	</ul>

</div>


<?php

        if ((isset($actionStatus)) && ($actionStatus == "success")) {
                echo <<<EOF
                        <div id="contentnorightbar">
                        <h9 id="Intro"> Success </h9>
                        <br/><br/>
                        <font color='#0000FF'>
EOF;
        echo $actionMsg;

        echo "</font></div>";

        }


        if ((isset($actionStatus)) && ($actionStatus == "failure")) {
                echo <<<EOF
                        <div id="contentnorightbar">
                        <h8 id="Intro"> Failure </h8>
                        <br/><br/>
                        <font color='#FF0000'>
EOF;
        echo $actionMsg;

        echo "</font></div>";

        }


?>
