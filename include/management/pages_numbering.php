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
//isset($_SERVER['PHP_SELF']) ? $self = $_SERVER['PHP_SELF'] : $self = "";

function setupLinks($pageNum, $maxPage, $orderBy, $orderType, $request1="", $request2="", $request3="") {

	// print 'previous' link only if we're not
	// on page one
	if ($pageNum > 1)       {
		$page = $pageNum - 1;
		$prev = " <a class='novisit' href=\"?page=$page&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">[Prev]</a> ";
		$first = "<a class='novisit' href=\"?page=1&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">[First Page]</a> ";
	} else {
		$prev  = ' [Prev] ';       // we're on page one, don't enable 'previous' link
		$first = ' [First Page] '; // nor 'first page' link
	}


	if ($pageNum < $maxPage) {
		$page = $pageNum + 1;
		$next = " <a class='novisit' href=\"?page=$page&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">[Next]</a> ";
		$last = " <a class='novisit' href=\"?page=$maxPage&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">[Last Page]</a> ";
	} else {
		$next = ' [Next] ';      // we're on the last page, don't enable 'next' link
		$last = ' [Last Page] '; // nor 'last page' link
	}


// print the page navigation link (simple view)
//echo $first . $prev . " &nbsp;&nbsp;&nbsp; page &nbsp; <strong>$pageNum</strong> of <strong>$maxPage</strong> &nbsp;&nbsp;&nbsp; " . $next . $last;
//echo "<br/><br/>";


// print the page navigation links in better looking layout
echo <<<EOT
<br/>
<table border='2' class='table1'>
<thead>
                <tr>
                <th class='info' colspan='10'>Navigation Links</th>
                </tr>
</thead>
<tr><td>
</td><td>
</td><td>
</td><td>
        $first
</td><td>
        $prev
</td><td>
        <strong> $pageNum </strong> of <strong> $maxPage </strong>
</td><td>
        $next
</td><td>
        $last
</td></tr>
</table>
<br/>




EOT;



}




function setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $request1="", $request2="", $request3="") {

	$numofpages = $numrows / $rowsPerPage;
	echo "<center>";
	for ($i = 1; $i <= $numofpages + 1; $i++) {
		if($i == $pageNum) {
			echo("&nbsp;<strong><font color='#FF0000'>".$i."</font></strong>&nbsp;");
		} else {
			echo("&nbsp; <a href=\"?page=$i&orderBy=$orderBy&orderType=$orderType$request1$request2$request3\">$i</a>&nbsp;");
		}
	}
	echo "</center>";

}

?>

