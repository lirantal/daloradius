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

function setupLinks($pageNum, $maxPage, $orderBy, $orderType, $request1="", $request2="", $request3="") {

	// print 'previous' link only if we're not
	// on page one
	if ($pageNum > 1)       {
		$page = $pageNum - 1;
		$prev = " <a href=\"?page=$page&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\"><img alt='[Prev]' src='images/icons/r.gif' border='0' /></a> ";
		$first = "<a href=\"?page=1&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\"><img alt='[First Page]' src='images/icons/rw.gif' border='0' /></a> ";
	} else {
		$prev = "<img alt=' [Prev] ' src='images/icons/r_non.gif' />";
		$first = "<img alt=' [First Page] ' src='images/icons/rw_non.gif' />";
	}


	if ($pageNum < $maxPage) {
		$page = $pageNum + 1;
		$next = " <a class='novisit' href=\"?page=$page&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\"><img alt='[Next]' src='images/icons/f.gif' border='0' /></a> ";
		$last = " <a class='tablenovisit' href=\"?page=$maxPage&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\"><img alt='[Next]' src='images/icons/ff.gif' border='0' /></a> ";

	} else {
		$next = "<img alt=' [Next] ' src='images/icons/f_non.gif' />";      // we're on the last page, don't enable 'next' link
		$last = "<img alt=' [Last Page] ' src='images/icons/ff_non.gif' />"; // nor 'last page' link
	}

	$greyColorBeg = "<font color='#5F5A59'>";
	$greyColorEnd = "</font>";

	echo "$greyColorBeg Page $pageNum $greyColorEnd of $greyColorBeg $maxPage $greyColorEnd<br/>";
	echo "$first $prev $next $last "; //$greyColorBeg $pageNum $greyColorEnd of $greyColorBeg $maxPage $greyColorEnd";
	echo "<br/>";

}




function setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $request1="", $request2="", $request3="") {

	$numofpages = ceil($numrows / $rowsPerPage);
	
	$str = '';
	if ($numofpages <= 20) {
		for ($i = 1; $i <= $numofpages; $i++) {
			if ($i == $pageNum) {
				$str .= "&nbsp;<strong><font color='#5F5A59' style='font-size: medium;'><a class=\"table\" href=\"?page=$i&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">$i</a></font></strong>&nbsp;";
			} else {
				$str .= "&nbsp; <a class=\"table\" href=\"?page=$i&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">$i</a>&nbsp;";
			}
		}
	} else {
		// 1st page
		$i = 1;
		$str .= "&nbsp; <a class=\"table\" href=\"?page=$i&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">$i</a>&nbsp;";
		
		if ($pageNum >= 1 && $pageNum <= 3) {
			$str .= "&nbsp; <a class=\"table\" href=\"?page=".($i+1)."&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">".($i+1)."</a>&nbsp;";
			$str .= "&nbsp; <a class=\"table\" href=\"?page=".($i+2)."&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">".($i+2)."</a>&nbsp;";
			$str .= "&nbsp; <a class=\"table\" href=\"?page=".($i+3)."&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">".($i+3)."</a>&nbsp;";
			$str .= '...';
		} else if ($pageNum <= $numofpages && $pageNum >= ($numofpages-2) ) {
			$str .= '...';
			$i = $numofpages;
			$str .= "&nbsp; <a class=\"table\" href=\"?page=".($i-3)."&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">".($i-3)."</a>&nbsp;";
			$str .= "&nbsp; <a class=\"table\" href=\"?page=".($i-2)."&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">".($i-2)."</a>&nbsp;";
			$str .= "&nbsp; <a class=\"table\" href=\"?page=".($i-1)."&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">".($i-1)."</a>&nbsp;";
		}  else {
			$str .= '...';
			$i = $pageNum;
			$str .= "&nbsp; <a class=\"table\" href=\"?page=".($i-2)."&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">".($i-2)."</a>&nbsp;";
			$str .= "&nbsp; <a class=\"table\" href=\"?page=".($i-1)."&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">".($i-1)."</a>&nbsp;";
			$str .= "&nbsp; <font color='#5F5A59' style='font-size: medium;'> <a class=\"table\" href=\"?page=".($i)."&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">".($i)."</a> </font>&nbsp;";
			$str .= "&nbsp; <a class=\"table\" href=\"?page=".($i+1)."&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">".($i+1)."</a>&nbsp;";
			$str .= "&nbsp; <a class=\"table\" href=\"?page=".($i+2)."&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">".($i+2)."</a>&nbsp;";
			$str .= '...';
		}
		
		// last page
		$i = $numofpages;
		$str .= "&nbsp; <a class=\"table\" href=\"?page=$i&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">$i</a>&nbsp;";
	}
	
	echo $str;
	

}

?>

