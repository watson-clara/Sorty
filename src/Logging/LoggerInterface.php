<?php
declare(strict_types=1);

namespace src\Logging;

/**
 * Interface for logging operations
 */
interface LoggerInterface
{
    /**
     * Log a message with a specified severity level
     *
     * @param string $message Message to log
     * @param string $level Severity level (INFO, WARNING, ERROR, etc.)
     * @return bool True if the log was successful
     */
    public function log(string $message, string $level = 'INFO'): bool;
} 