<?php
/*
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com>
 * SPDX-License-Identifier: GPL-2.0-or-later
 * UNIT-041: the unauthenticated legacy 2Checkout return flow is retired.
 */
function dalo_chilli_2checkout_retired() {
    http_response_code(410);
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo 'Legacy 2Checkout signup and payment callbacks are retired. No account activation is available.';
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    dalo_chilli_2checkout_retired();
}
