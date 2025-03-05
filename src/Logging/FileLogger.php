<?php
declare(strict_types=1);

namespace src\Logging;

/**
 * Logs messages to a file
 */
class FileLogger implements LoggerInterface
{
    private string $logFile;
    
    /**
     * Constructor
     *
     * @param string|null $logFile Path to the log file (optional)
     */
    public function __construct(?string $logFile = null)
    {
        $this->logFile = $logFile ?? __DIR__ . '/../../logs/app.log';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Log a message with a specified severity level
     *
     * @param string $message Message to log
     * @param string $level Severity level (INFO, WARNING, ERROR, etc.)
     * @return bool True if the log was successful
     */
    public function log(string $message, string $level = 'INFO'): bool
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
        
        return file_put_contents($this->logFile, $logEntry, FILE_APPEND) !== false;
    }
} 