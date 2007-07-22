<?php
/*********************************************************************
* Name: op_select_options.php
* Author: Liran tal <liran.tal@gmail.com>
* 
* This file is used by the management page (edit user) 
* and it's general purpose is to return the table string
* for a given attribute name
* 
* @param $attribute	The attribute name, Session-Timeout for example
* @return $table		The table name, either radcheck or radreply
*********************************************************************/

	function drawOptions() {
	
	echo "
		<option value='='>=</option>
		<option value=':='>:=</option>
		<option value='=='>==</option>
		<option value='+='>+=</option>
		<option value='!='>!=</option>
		<option value='>'>></option>
		<option value='>='>>=</option>
		<option value='<'><</option>
		<option value='<='><=</option>
		<option value='=~'>=~</option>
		<option value='!~'>!~</option>
		<option value='=*'>=*</option>
		<option value='!*'>!*</option>
		
	";
	
	}
?>
