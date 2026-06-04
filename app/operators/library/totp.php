<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 *
 * Operator TOTP helpers. This file wraps the vendored remotemerge/totp-php
 * library so the rest of daloRADIUS does not depend directly on that API.
 *********************************************************************************************************
 */

if (strpos($_SERVER['PHP_SELF'], '/library/totp.php') !== false) {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../../common/library/totp-php/autoload.php';
require_once __DIR__ . '/../../common/library/php-svg-qrcode/svg-qrcode.php';

use RemoteMerge\Totp\TotpFactory;
use RemoteMerge\Totp\TotpInterface;
use RemoteMerge\Totp\TotpException;

function dalo_totp_new(): TotpInterface {
    return TotpFactory::create(array(
        'algorithm' => 'sha1',
        'digits' => 6,
        'period' => 30,
        'max_discrepancy' => 1,
    ));
}

function dalo_totp_generate_secret(): string {
    return dalo_totp_new()->generateSecret();
}

function dalo_totp_generate_uri(string $secret, string $operator_username): string {
    return dalo_totp_new()->generateUri($secret, $operator_username, 'daloRADIUS');
}

function dalo_totp_generate_qr_svg_data_uri(string $otpauth_uri): string {
    $qr = new SVGQRCode($otpauth_uri, array('s' => 'qrm'));
    $svg = $qr->render_svg();

    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

function dalo_totp_verify(string $secret, string $code): bool {
    try {
        return dalo_totp_new()->verifyCode($secret, $code, 1);
    } catch (TotpException $e) {
        return false;
    }
}

function dalo_totp_verify_once(string $secret, string $code, ?int $last_counter): ?int {
    try {
        return dalo_totp_new()->verifyCodeOnce($secret, $code, $last_counter ?? -1, 1);
    } catch (TotpException $e) {
        return null;
    }
}

function dalo_totp_generate_recovery_codes(int $count = 8): array {
    $codes = array();
    for ($i = 0; $i < $count; $i++) {
        $raw = strtoupper(bin2hex(random_bytes(5)));
        $codes[] = substr($raw, 0, 5) . '-' . substr($raw, 5, 5);
    }
    return $codes;
}

function dalo_totp_hash_recovery_codes(array $codes): string {
    $hashes = array();
    foreach ($codes as $code) {
        $hashes[] = password_hash(strtoupper(trim($code)), PASSWORD_DEFAULT);
    }
    return json_encode($hashes);
}

function dalo_totp_verify_recovery_code(?string $encoded_hashes, string $code): array {
    if (empty($encoded_hashes)) {
        return array(false, $encoded_hashes);
    }

    $hashes = json_decode($encoded_hashes, true);
    if (!is_array($hashes)) {
        return array(false, $encoded_hashes);
    }

    $code = strtoupper(trim($code));
    foreach ($hashes as $idx => $hash) {
        if (is_string($hash) && password_verify($code, $hash)) {
            unset($hashes[$idx]);
            return array(true, json_encode(array_values($hashes)));
        }
    }

    return array(false, $encoded_hashes);
}
?>
