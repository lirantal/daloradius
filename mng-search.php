<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include('library/check_operator_perm.php');


	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "id";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";

	isset($_REQUEST['username']) ? $username = $_REQUEST['username'] : $username = "%";

	$search_username = $username; //feed the sidebar variables
	$username = str_replace('*', '%', $username);

	include_once('library/config_read.php');
	$log = "visited page: ";
	
?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" href="css/form-field-tooltip.css" type="text/css" media="screen,projection" />
</head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script src="library/javascript/rounded-corners.js" type="text/javascript"></script>
<script src="library/javascript/form-field-tooltip.js" type="text/javascript"></script>
<script type="text/javascript" src="library/javascript/ajax.js"></script>
<script type="text/javascript" src="library/javascript/ajaxGeneric.js"></script>
<?php

	include ("menu-mng-users.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngsearch.php']; ?>
				:: <?php if (isset($username)) { echo $username; } ?><h144>+</h144></a></h2>
				
				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo "searched for user $username" ?><br/>
				</div>

<br/>

<?php

        include 'include/management/pages_common.php';
	include 'library/opendb.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file
	
	//orig: used as method to get total rows - this is required for the pages_numbering.php page
	$sql = "SELECT distinct(Username) as UserName, value, id FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName like 
'".$dbSocket->escapeSimple($username)."%' GROUP BY UserName";
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();

	$sql = "SELECT distinct(Username) as UserName, value, id FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName like 
'".$dbSocket->escapeSimple($username)."%' GROUP BY UserName ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";

	
	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	/* END */

        echo "<form name='searchusers' method='post' action='' >";

	echo "<table border='0' class='table1'>\n";
	echo "
					<thead>
                                                        <tr>
                                                        <th colspan='10' align='left'>

                                Select:
                                <a class=\"table\" href=\"javascript:SetChecked(1,'username[]','searchusers')\">All</a>

                                <a class=\"table\" href=\"javascript:SetChecked(0,'username[]','searchusers')\">None</a>
                        <br/>
                                <input class='button' type='button' value='Delete' onClick='javascript:removeUserCheckbox(\"searchusers\")' />
                                <br/><br/>
	";

	if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
		setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);

	echo " </th></tr>
					</thead>

			";


        if ($orderType == "asc") {
                $orderType = "desc";
        } else  if ($orderType == "desc") {
                $orderType = "asc";
        }

	echo "<thread> <tr>
		<th scope='col'> 
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=id&orderType=$orderType\">
		".$l['all']['ID']. "</a>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=Username&orderType=$orderType\">
	 	".$l['all']['Username']."</a>
		</th>

		<th scope='col'> 
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&orderBy=Value&orderType=$orderType\">
		".$l['all']['Password']."</a>
		</th>
	</tr> </thread>";
	while($row = $res->fetchRow()) {
		printqn("<tr>
			<td> <input type='checkbox' name='username[]' value='$row[0]'> $row[2] </td>
                        <td> <a class='tablenovisit' href='javascript:return;'
                                onClick='javascript:ajaxGeneric(\"include/management/retUserinfo\",\"retBandwidthInfo\",\"divContainer\",\"username=$row[0]\");
                                        javascript:__displayTooltip();'
                                tooltipText='
                                        <a class=\"toolTip\" href=\"mng-edit.php?username=$row[0]\">
	                                        {$l['Tooltip']['UserEdit']}
                                        </a>&nbsp
					<br/>
					<a class=\"toolTip\" href=\"config-maint-test-user.php?username=$row[0]&password=$row[1]\">
						{$l['all']['TestUser']}
					</a>&nbsp
					 <a class=\"toolTip\" href=\"acct-username.php?username=$row[0]\">
						{$l['all']['Accounting']}
					</a>
                                        <br/><br/>

                                        <div id=\"divContainer\">
                                                Loading...
                                        </div>
                                        <br/>'
                                >$row[0]</a>
                        </td>

			<td> $row[1] </td>

			</tr>");

	}

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='10' align='left'>
        ";
        setupLinks($pageNum, $maxPage, $orderBy, $orderType);
        echo "
                                                        </th>
                                                        </tr>
                                        </tfoot>
                ";


	echo "</table>";

	include 'library/closedb.php';

?>


<?php
	include('include/config/logging.php');
?>
		
		</div>
		
		<div id="footer">
		
								<?php
        include 'page-footer.php';
?>

		
		</div>
		
</div>
</div>

<script type="text/javascript">
	var tooltipObj = new DHTMLgoodies_formTooltip();
	tooltipObj.setTooltipPosition('right');
	tooltipObj.setPageBgColor('#EEEEEE');
	tooltipObj.setTooltipCornerSize(15);
	tooltipObj.initFormFieldTooltip();
</script>


</body>
</html>

