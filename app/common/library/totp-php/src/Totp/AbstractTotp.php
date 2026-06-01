<?php

declare(strict_types=1);

namespace RemoteMerge\Totp;

use RemoteMerge\Message\MessageStore;
use RemoteMerge\Utils\Base32;

abstract class AbstractTotp
{
    /**
     * The hash algorithm to use for HMAC.
     */
    protected string $algorithm = 'sha1';

    /**
     * The length of the TOTP code.
     */
    protected int $digits = 6;

    /**
     * The duration of a time slice in seconds.
     */
    protected int $period = 30;

    /**
     * The maximum allowed discrepancy value.
     */
    protected int $maxDiscrepancy = 10;

    /**
     * The supported hash algorithms.
     */
    protected const SUPPORTED_ALGORITHMS = ['sha1', 'sha256', 'sha512'];

    /**
     * Initializes the TOTP instance with optional configuration options.
     *
     * @param array<string, mixed> $options An associative array of configuration options.
     *        Supported options: 'max_discrepancy' (int).
     */
    public function __construct(array $options = [])
    {
        if (isset($options['max_discrepancy'])) {
            $this->maxDiscrepancy = (int) $options['max_discrepancy'];
        }
    }

    /**
     * Validates the secret key.
     *
     * @param string $secret The secret key to validate.
     * @throws TotpException If the secret key is invalid.
     */
    protected function validateSecret(string $secret): void
    {
        // Check if the secret is empty
        if ($secret === '') {
            throw new TotpException(MessageStore::get('validation.secret_empty'));
        }

        // Check length divisibility by 8 (existing validation)
        if (strlen($secret) % 8 !== 0) {
            throw new TotpException(MessageStore::get('validation.secret_length'));
        }

        // Base32 validation: A-Z, 2-7, and RFC 4648 padding
        if (!Base32::isValidUpper($secret)) {
            throw new TotpException(MessageStore::get('validation.secret_characters'));
        }

        // Warn about weak secrets without throwing
        $decoded = Base32::decodeUpper($secret);
        $byteLength = strlen($decoded);

        if ($byteLength < 20) {
            error_log(MessageStore::get('security.weak_secret_log', $byteLength));
        }
    }

    /**
     * Validates the TOTP code.
     *
     * @param string $code The TOTP code to validate.
     * @throws TotpException If the code is invalid.
     */
    protected function validateCode(string $code): void
    {
        if (preg_match('/^\d{' . $this->digits . '}$/', $code) !== 1) {
            throw new TotpException(MessageStore::get('validation.code_format', $this->digits));
        }
    }

    /**
     * Gets the current time slice based on the current time and the time slice duration.
     *
     * @return int The current time slice.
     */
    protected function getCurrentTimeSlice(): int
    {
        return (int) floor(time() / $this->period);
    }

    /**
     * Packs the time slice into a binary string.
     *
     * @param int $timeSlice The time slice to pack.
     * @return string The packed binary string (8 bytes, big-endian unsigned 64-bit integer).
     */
    protected function packTimeSlice(int $timeSlice): string
    {
        return pack('N2', 0, $timeSlice);
    }

    /**
     * Extracts the TOTP code from the HMAC hash.
     *
     * @param string $hash The HMAC hash.
     * @param int $offset The offset to start extracting the code from.
     * @return int The extracted TOTP code.
     */
    protected function extractCodeFromHash(string $hash, int $offset): int
    {
        // Extract the hash values
        $hash1 = ord($hash[$offset]) & 0x7f;
        $hash2 = ord($hash[$offset + 1]) & 0xff;
        $hash3 = ord($hash[$offset + 2]) & 0xff;
        $hash4 = ord($hash[$offset + 3]) & 0xff;

        return (($hash1 << 24) | ($hash2 << 16) | ($hash3 << 8) | $hash4) % (10 ** $this->digits);
    }
}
