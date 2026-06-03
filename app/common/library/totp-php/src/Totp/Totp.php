<?php

declare(strict_types=1);

namespace RemoteMerge\Totp;

use Exception;
use RemoteMerge\Message\MessageStore;
use RemoteMerge\Utils\Base32;

final class Totp extends AbstractTotp implements TotpInterface
{
    /**
     * Configures the TOTP parameters.
     *
     * @param array<string, mixed> $options An associative array of configuration options.
     *        Supported options: 'algorithm' (string), 'digits' (int), 'period' (int).
     * @throws TotpException If an unsupported algorithm is provided or if options are invalid.
     */
    public function configure(array $options): void
    {
        if (isset($options['algorithm'])) {
            $selectedAlgorithm = strtolower((string) $options['algorithm']);

            if (!in_array($selectedAlgorithm, self::SUPPORTED_ALGORITHMS, true)) {
                throw new TotpException(MessageStore::get('configuration.unsupported_algorithm'));
            }

            $this->algorithm = $selectedAlgorithm;
        }

        if (isset($options['digits'])) {
            if (!in_array($options['digits'], [6, 8], true)) {
                throw new TotpException(MessageStore::get('configuration.invalid_digits'));
            }

            $this->digits = $options['digits'];
        }

        if (isset($options['period'])) {
            if (!is_int($options['period']) || $options['period'] <= 0) {
                throw new TotpException(MessageStore::get('configuration.invalid_period'));
            }

            $this->period = $options['period'];
        }
    }

    /**
     * Gets the hash algorithm to use for HMAC.
     *
     * @return string The hash algorithm.
     */
    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    /**
     * Gets the length of the TOTP code.
     *
     * @return int The length of the TOTP code.
     */
    public function getDigits(): int
    {
        return $this->digits;
    }

    /**
     * Gets the duration of a time slice in seconds.
     *
     * @return int The duration of a time slice.
     */
    public function getPeriod(): int
    {
        return $this->period;
    }

    /**
     * Generates a secret key for TOTP.
     *
     * @throws Exception If an error occurs generating the secret key.
     * @return string The generated secret key in Base32 format.
     */
    public function generateSecret(): string
    {
        return Base32::encodeUpper(random_bytes(20));
    }

    /**
     * Gets the TOTP code for the given secret.
     *
     * @param string $secret The secret key in Base32 format.
     * @param int|null $timeSlice The time slice to generate the code for. Defaults to the current time slice.
     * @throws TotpException If the secret key is invalid.
     * @return string The generated TOTP code.
     */
    public function getCode(string $secret, ?int $timeSlice = null): string
    {
        $this->validateSecret($secret);

        $timeSlice ??= $this->getCurrentTimeSlice();
        $decodedSecret = Base32::decodeUpper($secret);

        return $this->getCodeFromDecodedSecret($decodedSecret, $timeSlice);
    }

    /**
     * Gets the TOTP code for an already decoded secret.
     *
     * @param string $decodedSecret The decoded binary secret key.
     * @param int $timeSlice The time slice to generate the code for.
     * @return string The generated TOTP code.
     */
    private function getCodeFromDecodedSecret(string $decodedSecret, int $timeSlice): string
    {
        $time = $this->packTimeSlice($timeSlice);

        $hash = hash_hmac($this->algorithm, $time, $decodedSecret, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0f;

        $code = $this->extractCodeFromHash($hash, $offset);

        return str_pad((string) $code, $this->digits, '0', STR_PAD_LEFT);
    }

    /**
     * Verifies the TOTP code for the given secret.
     *
     * @param string $secret The secret key in Base32 format.
     * @param string $code The code to verify.
     * @param int $discrepancy The allowed discrepancy in the code. Defaults to 1.
     * @param int|null $timeSlice The time slice to verify the code for. Defaults to the current time slice.
     * @throws TotpException If the secret key is invalid or discrepancy is out of range.
     * @return bool True if the code is valid, false otherwise.
     */
    public function verifyCode(string $secret, string $code, int $discrepancy = 1, ?int $timeSlice = null): bool
    {
        if ($discrepancy < 0 || $discrepancy > $this->maxDiscrepancy) {
            throw new TotpException(MessageStore::get('configuration.invalid_discrepancy', $this->maxDiscrepancy));
        }

        $this->validateSecret($secret);
        $this->validateCode($code);

        $currentSlice = $timeSlice ?? $this->getCurrentTimeSlice();
        $decodedSecret = Base32::decodeUpper($secret);

        for ($offset = -$discrepancy; $offset <= $discrepancy; ++$offset) {
            if (hash_equals($this->getCodeFromDecodedSecret($decodedSecret, $currentSlice + $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifies the TOTP code while preventing replay attacks.
     *
     * Skips any time slices at or below the last accepted slice, ensuring a
     * previously used code cannot be reused.
     *
     * @param string $secret The secret key in Base32 format.
     * @param string $code The code to verify.
     * @param int $lastAcceptedSlice The last time slice that was successfully accepted.
     * @param int $discrepancy The allowed discrepancy in time slices. Defaults to 1.
     * @throws TotpException If the secret key is invalid or discrepancy is out of range.
     * @return int|null The matched time slice if valid, or null if invalid or replay detected.
     */
    public function verifyCodeOnce(string $secret, string $code, int $lastAcceptedSlice, int $discrepancy = 1): ?int
    {
        if ($discrepancy < 0 || $discrepancy > $this->maxDiscrepancy) {
            throw new TotpException(MessageStore::get('configuration.invalid_discrepancy', $this->maxDiscrepancy));
        }

        $this->validateSecret($secret);
        $this->validateCode($code);

        $currentSlice = $this->getCurrentTimeSlice();
        $decodedSecret = Base32::decodeUpper($secret);

        for ($offset = -$discrepancy; $offset <= $discrepancy; ++$offset) {
            $candidateSlice = $currentSlice + $offset;

            if ($candidateSlice <= $lastAcceptedSlice) {
                continue;
            }

            if (hash_equals($this->getCodeFromDecodedSecret($decodedSecret, $candidateSlice), $code)) {
                return $candidateSlice;
            }
        }

        return null;
    }

    /**
     * Audits a secret key and returns diagnostic security information.
     *
     * This method never throws exceptions; all issues are reported via the
     * returned array so callers can handle them gracefully.
     *
     * @param string $secret The secret key in Base32 format to audit.
     * @return array{length_bytes: int, is_strong: bool, warnings: list<string>} Diagnostic information.
     */
    public function auditSecret(string $secret): array
    {
        $warnings = [];
        $lengthBytes = 0;

        if ($secret === '') {
            $warnings[] = MessageStore::get('security.audit_secret_empty');

            return [
                'length_bytes' => 0,
                'is_strong' => false,
                'warnings' => $warnings,
            ];
        }

        // Validate a Base32 format without throwing
        if (!Base32::isValidUpper($secret)) {
            $warnings[] = MessageStore::get('security.audit_invalid_base32');

            return [
                'length_bytes' => 0,
                'is_strong' => false,
                'warnings' => $warnings,
            ];
        }

        $decoded = Base32::decodeUpper($secret);
        $lengthBytes = strlen($decoded);

        if ($lengthBytes < 20) {
            $warnings[] = MessageStore::get('security.audit_weak_secret', $lengthBytes);
        }

        return [
            'length_bytes' => $lengthBytes,
            'is_strong' => $lengthBytes >= 20,
            'warnings' => $warnings,
        ];
    }

    /**
     * Generates a TOTP URI for QR code generation.
     *
     * @param string $secret The secret key in Base32 format.
     * @param string $label The label for the account (e.g., user@example.com).
     * @param string $issuer The issuer of the TOTP (e.g., the service name).
     * @throws TotpException If the secret key is invalid.
     * @return string The TOTP URI in the format `otpauth://totp/{issuer}:{label}?secret={secret}&issuer={issuer}&algorithm={ALGORITHM}&digits={digits}&period={period}`.
     *               The algorithm is returned in uppercase (e.g., SHA1, SHA256, SHA512) per the Key URI Format specification.
     */
    public function generateUri(string $secret, string $label, string $issuer): string
    {
        $this->validateSecret($secret);

        $strUri = 'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=%s&digits=%d&period=%d';
        $encodedLabel = rawurlencode($label);
        $encodedIssuer = rawurlencode($issuer);

        return sprintf($strUri, $encodedIssuer, $encodedLabel, $secret, $encodedIssuer, strtoupper($this->algorithm), $this->digits, $this->period);
    }
}
