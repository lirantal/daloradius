<?php

declare(strict_types=1);

namespace RemoteMerge\Totp;

interface TotpInterface
{
    /**
     * Configures the TOTP parameters.
     *
     * @param array<string, mixed> $options An associative array of configuration options.
     *        Supported options: 'algorithm' (string), 'digits' (int), 'period' (int).
     */
    public function configure(array $options): void;

    /**
     * Gets the hash algorithm to use for HMAC.
     */
    public function getAlgorithm(): string;

    /**
     * Gets the length of the TOTP code.
     */
    public function getDigits(): int;

    /**
     * Gets the duration of a time slice in seconds.
     */
    public function getPeriod(): int;

    /**
     * Generates a new secret key for TOTP.
     *
     * @return string The generated secret key, typically in Base32 format.
     */
    public function generateSecret(): string;

    /**
     * Generates a TOTP code for the given secret and optional time slice.
     *
     * @param string $secret The secret key.
     * @param int|null $timeSlice The time slice to use for code generation.
     *        If null, the current time slice is used.
     * @return string The generated TOTP code.
     */
    public function getCode(string $secret, ?int $timeSlice = null): string;

    /**
     * Verifies a TOTP code against the given secret.
     *
     * @param string $secret The secret key.
     * @param string $code The TOTP code to verify.
     * @param int $discrepancy The allowed number of time slices for discrepancy.
     *        For example, a discrepancy of 1 allows the code to be valid for the previous,
     *        current, and next time slices.
     * @param int|null $timeSlice The time slice to use for verification.
     *        If null, the current time slice is used.
     * @return bool True if the code is valid, false otherwise.
     */
    public function verifyCode(string $secret, string $code, int $discrepancy = 1, ?int $timeSlice = null): bool;

    /**
     * Generates a TOTP URI for QR code generation.
     *
     * @param string $secret The secret key.
     * @param string $label The label for the account (e.g., user@example.com).
     * @param string $issuer The issuer of the TOTP (e.g., Abc Corp).
     * @return string The TOTP URI in the format `otpauth://totp/{issuer}:{label}?secret={secret}&issuer={issuer}&algorithm={ALGORITHM}&digits={digits}&period={period}`.
     *               The algorithm is returned in uppercase (e.g., SHA1, SHA256, SHA512) per the Key URI Format specification.
     */
    public function generateUri(string $secret, string $label, string $issuer): string;
}
