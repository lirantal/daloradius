<?php
/*********************************************************************
* Name: pages_numbering.php
* Author: Liran tal <liran.tal@gmail.com>
*
* This script provides support for spanning alot of table results across several
* pages with full numbering support, first and last links, etc...
*
*********************************************************************/


/* Should be called after the include for opendb and before the $sql decleration
and execution. */

$rowsPerPage = $configValues['CONFIG_IFACE_TABLES_LISTING'];
$pageNum = 1;

if(isset($_REQUEST['page'])) {
	$pageNum = $_REQUEST['page'];
}

$offset = ($pageNum - 1) * $rowsPerPage;
$self = $_SERVER['PHP_SELF'];

function setupLinks($pageNum, $maxPage, $orderBy, $orderType) {

	// print 'previous' link only if we're not
	// on page one
	if ($pageNum > 1)       {
		$page = $pageNum - 1;
		$prev = " <a href=\"$self?page=$page&orderBy=$orderBy&orderType=$orderType\">[Prev]</a> ";

		$first = " <a href=\"$self?page=1&orderBy=$orderBy&orderType=$orderType\">[First Page]</a> ";
	} else {
		$prev  = ' [Prev] ';       // we're on page one, don't enable 'previous' link
		$first = ' [First Page] '; // nor 'first page' link
	}


	if ($pageNum < $maxPage) {
		$page = $pageNum + 1;
		$next = " <a href=\"$self?page=$page&orderBy=$orderBy&orderType=$orderType\">[Next]</a> ";

		$last = " <a href=\"$self?page=$maxPage&orderBy=$orderBy&orderType=$orderType\">[Last Page]</a> ";
	} else {
		$next = ' [Next] ';      // we're on the last page, don't enable 'next' link
		$last = ' [Last Page] '; // nor 'last page' link
	}


// print the page navigation link
echo $first . $prev . " &nbsp;&nbsp;&nbsp; page &nbsp; <strong>$pageNum</strong> of <strong>$maxPage</strong> &nbsp;&nbsp;&nbsp; " . $next . $last;
echo "<br/><br/>";

}




function setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType) {

	$numofpages = $numrows / $rowsPerPage;
	for ($i = 1; $i <= $numofpages; $i++) {
		if($i == $pageNum) {
			echo("&nbsp;".$i."&nbsp;");
		} else {
			echo("&nbsp; <a href=\"$PHP_SELF?page=$i&orderBy=$orderBy&orderType=$orderType\">$i</a>&nbsp;");
		}
	}

}

?>

