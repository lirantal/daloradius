<?php
/*********************************************************************
* Name: provisionUser.php
* Author: Liran tal <liran.tal@gmail.com>
*
* Provision user in database according to plan information
*
*********************************************************************/

require_once __DIR__ . '/retired.php';
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    dalo_chilli_2checkout_retired();
    exit;
}

/** @deprecated Legacy 2Checkout provisioning is deliberately unavailable. */
function provisionUser($dbSocket, $txnId) {
    throw new LogicException('Legacy 2Checkout provisioning is retired');
}
