<?php
/*
 * Minimal autoloader for the vendored remotemerge/totp-php runtime files.
 * daloRADIUS intentionally vendors this small dependency instead of requiring Composer.
 */

require_once __DIR__ . '/src/Data/messages.php';
require_once __DIR__ . '/src/Message/MessageInterface.php';
require_once __DIR__ . '/src/Message/MessageStore.php';
require_once __DIR__ . '/src/Totp/TotpException.php';
require_once __DIR__ . '/src/Utils/Base32.php';
require_once __DIR__ . '/src/Totp/TotpInterface.php';
require_once __DIR__ . '/src/Totp/AbstractTotp.php';
require_once __DIR__ . '/src/Totp/Totp.php';
require_once __DIR__ . '/src/Totp/TotpFactory.php';
