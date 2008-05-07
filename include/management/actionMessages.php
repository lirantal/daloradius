<?php
/*********************************************************************
* Name: actionMessages.php
* Author: Liran tal <liran.tal@gmail.com>
*
* This file provides control for messages that are printed to the
* screen in reply to actions such as applying forms, saving data,
* removing data and such.
*
*********************************************************************/


if (isset($failureMsg)) {
	echo "<div class='failure'>
		$failureMsg
	</div
	";
}


if (isset($successMsg)) {
	echo "<div class='success'>
		$successMsg
	</div
	";
}

