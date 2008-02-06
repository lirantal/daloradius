<?php
/*******************************************************************
* Extension name: exten radius  server information                 *
*                                                                  *
* Description:                                                     *
* this script runs a check to see if freeradius is up and running  *
* the check is done by looking for a 'radius' process listening    *
* on any socket interface. clumsy, but that's what we got for now. *
*                                                                  *
* Author: Liran Tal <liran@enginx.com>                             *
*                                                                  *
*******************************************************************/

function check_service($sname) {
	if ($sname != '') {
		system("pgrep ".escapeshellarg($sname)." >/dev/null 2>&1", $ret_service);
		if ($ret_service == 0) {
			return "Enabled";
		} else {
			return "Disabled";
		}
	} else {
		return "no service name";
	}
}

?>

<?php
echo "<h3>Service Status</h3>";
?>

<table class='summarySection'>
  <tr>
    <td class='summaryKey'> Radius </td>
    <td class='summaryValue'><span class='sleft'><?php echo check_service("radius"); ?></span> </td>
  </tr>
  <tr>
    <td class='summaryKey'> Mysql </td>
    <td class='summaryValue'><span class='sleft'><?php echo check_service("mysql");  ?></span> </td>
  </tr>
</table>
