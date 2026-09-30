<?php
/*********************************************************************
 * Name: provisionUser.php
 * Author: Liran tal <liran.tal@gmail.com>
 * Provision user in database according to plan information
 *********************************************************************/
/* daloRADIUS — GPL-2.0-or-later.
 * UNIT-040 relocates provisioning, authorization and billing to the shared
 * PDO-only provider. Legacy three-argument PEAR entry points are retired;
 * no other repository callers exist. The IPN callback invokes the complete
 * verified transaction rather than a helper that opens another connection.
 */
require_once dirname(__DIR__, 4) . '/common/paypalPdo.php';
