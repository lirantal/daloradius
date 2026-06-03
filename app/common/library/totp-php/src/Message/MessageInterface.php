<?php

declare(strict_types=1);

namespace RemoteMerge\Message;

/**
 * Interface for message retrieval services.
 */
interface MessageInterface
{
    /**
     * Retrieves a message by its key with optional parameter substitution.
     *
     * @param string $key The message key using dot notation (e.g., 'validation.secret_empty').
     * @param mixed ...$params Optional parameters for sprintf-style message formatting.
     */
    public static function get(string $key, ...$params): string;

    /**
     * Checks if a message key exists in the message store.
     *
     * @param string $key The message key to check using dot notation.
     */
    public static function has(string $key): bool;
}
