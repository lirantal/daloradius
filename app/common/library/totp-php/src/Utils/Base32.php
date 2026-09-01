<?php

declare(strict_types=1);

namespace RemoteMerge\Utils;

use RemoteMerge\Message\MessageStore;
use RemoteMerge\Totp\TotpException;

final class Base32
{
    /**
     * Base32 character set (RFC 4648)
     */
    private const ENCODE_MAP = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Pre-computed decode lookup table for O(1) character mapping.
     */
    private const DECODE_MAP = [
        'A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4, 'F' => 5, 'G' => 6, 'H' => 7,
        'I' => 8, 'J' => 9, 'K' => 10, 'L' => 11, 'M' => 12, 'N' => 13, 'O' => 14, 'P' => 15,
        'Q' => 16, 'R' => 17, 'S' => 18, 'T' => 19, 'U' => 20, 'V' => 21, 'W' => 22, 'X' => 23,
        'Y' => 24, 'Z' => 25, '2' => 26, '3' => 27, '4' => 28, '5' => 29, '6' => 30, '7' => 31,
    ];

    /** Mask to extract 8 bits (1 byte) from a buffer */
    private const BYTE_MASK = 0xFF;

    /** Mask to extract 5 bits for Base32 character mapping */
    private const BASE32_MASK = 0x1F;

    /** Number of bits in a standard byte */
    private const BITS_PER_BYTE = 8;

    /** Number of bits represented by each Base32 character */
    private const BITS_PER_BASE32 = 5;

    /** Standard Base32 block size for padding calculations */
    private const BASE32_BLOCK_SIZE = 8;

    /** Valid padding lengths per RFC 4648 (0, 1, 3, 4, or 6 '=' characters) */
    private const BASE32_VALID_PADDING = [0, 1, 3, 4, 6];

    /**
     * Encodes binary data to Base32 using optimized bit manipulation.
     *
     * @param string $data The binary data to encode.
     * @return string The Base32 encoded string.
     */
    public static function encodeUpper(string $data): string
    {
        if ($data === '') {
            return '';
        }

        $length = strlen($data);
        $output = '';
        $buffer = 0;
        $bufferLength = 0;

        // Process input byte by byte using bit manipulation
        for ($i = 0; $i < $length; $i++) {
            $buffer = ($buffer << self::BITS_PER_BYTE) | ord($data[$i]);
            $bufferLength += self::BITS_PER_BYTE;

            // Extract 5-bit chunks and encode them
            while ($bufferLength >= self::BITS_PER_BASE32) {
                $bufferLength -= self::BITS_PER_BASE32;
                $output .= self::ENCODE_MAP[($buffer >> $bufferLength) & self::BASE32_MASK];
            }
        }

        // Handle remaining bits if any
        if ($bufferLength > 0) {
            $output .= self::ENCODE_MAP[($buffer << (self::BITS_PER_BASE32 - $bufferLength)) & self::BASE32_MASK];
        }

        // Add RFC 4648 compliant padding
        $padLength = (self::BASE32_BLOCK_SIZE - (strlen($output) % self::BASE32_BLOCK_SIZE)) % self::BASE32_BLOCK_SIZE;
        if ($padLength > 0) {
            $output .= str_repeat('=', $padLength);
        }

        return $output;
    }

    /**
     * Decodes a Base32 encoded string to binary data using optimized lookup.
     *
     * @param string $data The Base32 encoded string.
     * @throws TotpException If the input is not a valid Base32 string.
     * @return string The decoded binary data.
     */
    public static function decodeUpper(string $data): string
    {
        if ($data === '') {
            return '';
        }

        // Validate input
        if (!self::isValidUpper($data)) {
            throw new TotpException(MessageStore::get('encoding.invalid_base32_char', '='));
        }

        // Remove padding
        $data = rtrim($data, '=');
        $length = strlen($data);
        $output = '';
        $buffer = 0;
        $bufferLength = 0;

        // Process each character using a pre-computed lookup table
        for ($i = 0; $i < $length; $i++) {
            $buffer = ($buffer << self::BITS_PER_BASE32) | self::DECODE_MAP[$data[$i]];
            $bufferLength += self::BITS_PER_BASE32;

            // Extract complete bytes
            if ($bufferLength >= self::BITS_PER_BYTE) {
                $bufferLength -= self::BITS_PER_BYTE;
                $output .= chr(($buffer >> $bufferLength) & self::BYTE_MASK);
            }
        }

        return $output;
    }

    /**
     * Checks whether a string is valid uppercase RFC 4648 Base32.
     *
     * @param string $data The Base32 encoded string.
     */
    public static function isValidUpper(string $data): bool
    {
        // Encoded output is always a multiple of 8 characters
        if (strlen($data) % self::BASE32_BLOCK_SIZE !== 0) {
            return false;
        }

        $unpadded = rtrim($data, '=');
        $paddingCount = strlen($data) - strlen($unpadded);

        // Check padding chars are valid per RFC 4648 §6
        if (!in_array($paddingCount, self::BASE32_VALID_PADDING, true)) {
            return false;
        }

        // Validate against the Base32 alphabet
        return preg_match('/^[A-Z2-7]*$/', $unpadded) === 1;
    }
}
