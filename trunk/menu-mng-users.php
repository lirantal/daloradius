<body>
<?php
    include_once("lang/main.php");
?>
<div id="wrapper">
<div id="innerwrapper">

<?php
    $m_active = "Management";
    include_once("include/menu/menu-items.php");
	include_once("include/menu/management-subnav.php");
?>

<div id="sidebar">

	<h2>Management</h2>
	
	<h3>Users Management</h3>
	<ul class="subnav">
	
		<li><a href="mng-list-all.php">
			<img src='images/icons/userList.gif' border='0'>
			&nbsp;<b>&raquo;</b>
			<?php echo $l['button']['ListUsers'] ?></a>
		</li>
		<li><a href="mng-new.php">
			<img src='images/icons/userNew.gif' border='0'>
			&nbsp;<b>&raquo;</b>
			<?php echo $l['button']['NewUser'] ?></a>
		</li>
		<li><a href="mng-new-quick.php">
			<img src='images/icons/userNew.gif' border='0'>
			&nbsp;<b>&raquo;</b>
			<?php echo $l['button']['NewUserQuick'] ?></a>
		</li>
		<li><a href="mng-batch.php">
			<img src='images/icons/userNew.gif' border='0'>
			&nbsp;<b>&raquo;</b>
			<?php echo $l['button']['BatchAddUsers'] ?><a>
		</li>
		<li><a href="javascript:document.mngedit.submit();"">
			<img src='images/icons/userEdit.gif' border='0'>
			&nbsp;<b>&raquo;</b>
			<?php echo $l['button']['EditUser'] ?><a>
			<form name="mngedit" action="mng-edit.php" method="get" class="sidebar">
			<input name="username" type="text" id="usernameEdit" autocomplete="off"
				onClick='javascript:__displayTooltip();' 
				tooltipText='<?php echo $l['Tooltip']['Username']; ?> <br/>'
				value="<?php if (isset($edit_username)) echo $edit_username; ?>" tabindex=1>
			</form></li>
		<li><a href="javascript:document.mngsearch.submit();"">
			<img src='images/icons/userSearch.gif' border='0'>&nbsp;<b>&raquo;</b><?php echo $l['button']['SearchUsers'] ?><a>
			<form name="mngsearch" action="mng-search.php" method="get" class="sidebar">
			<input name="username" type="text" id="usernameSearch" autocomplete="off"
				onClick='javascript:__displayTooltip();' 
				tooltipText='<?php echo $l['Tooltip']['Username']; ?> <br/> <?php echo $l['Tooltip']['UsernameWildcard']; ?>'
				value="<?php if (isset($search_username)) echo $search_username; ?>" tabindex=2>
			</form></li>
		
		<li><a href="mng-del.php">
			<img src='images/icons/userRemove.gif' border='0'>&nbsp;<b>&raquo;</b><?php echo $l['button']['RemoveUsers'] ?></a></li>
		
	</ul>

	<br/><br/>
	<h2>Search</h2>
	
	<input name="" type="text" value="Search" tabindex=4 />

</div>

<?php 
	include_once("include/management/autocomplete.php");

	if ($autoComplete) {
		echo "<script type=\"text/javascript\">
			/** Making usernameEdit interactive **/
	              autoComEdit = new DHTMLSuite.autoComplete();
	              autoComEdit.add('usernameEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');

			/** Making usernameSearch interactive **/
	              autoComSearch = new DHTMLSuite.autoComplete();
	              autoComSearch.add('usernameSearch','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');
	              </script>";
	} 
?>
