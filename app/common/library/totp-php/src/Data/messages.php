<?php

declare(strict_types=1);

/**
 * Message definitions for the TOTP library.
 */

return [
    /**
     * Validation error messages.
     */
    'validation' => [
        'secret_empty' => 'The secret key cannot be empty.',
        'secret_length' => 'The secret key is invalid. Its length must be a multiple of 8.',
        'secret_characters' => 'The secret key contains invalid characters.',
        'code_format' => 'The code must be a %d-digit number.',
    ],

    /**
     * Configuration error messages.
     */
    'configuration' => [
        'unsupported_algorithm' => 'Unsupported hash algorithm.',
        'invalid_digits' => 'Digits must be either 6 or 8.',
        'invalid_period' => 'Period must be a positive integer.',
        'invalid_discrepancy' => 'Discrepancy must be between 0 and %d.',
    ],

    /**
     * Encoding and decoding error messages.
     */
    'encoding' => [
        'invalid_base32_char' => 'Invalid Base32 character: %s',
    ],

    /**
     * Security warning messages.
     */
    'security' => [
        'weak_secret_log' => 'TOTP Security Warning: Weak secret detected (%d bytes, recommend >= 20 bytes)',
        'audit_secret_empty' => 'Secret is empty (0 bytes).',
        'audit_invalid_base32' => 'Secret is not valid Base32 format.',
        'audit_weak_secret' => 'Secret is weak (%d bytes); recommend >= 20 bytes for adequate security.',
    ],
];
