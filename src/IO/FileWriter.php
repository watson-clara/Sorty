<?php
declare(strict_types=1);

namespace src\IO;

use src\Logging\LoggerInterface;
use RuntimeException;

/**
 * Writes arrays of lines to files
 */
class FileWriter implements FileWriterInterface
{
    private LoggerInterface $logger;
    
    /**
     * Constructor
     *
     * @param LoggerInterface $logger Logger for recording operations
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * Write an array of lines to a file
     *
     * @param string $filePath Path to the file to write
     * @param array $lines Array of lines to write
     * @return bool True if the write was successful
     * @throws RuntimeException If the file cannot be written
     */
    public function writeLines(string $filePath, array $lines): bool
    {
        // Ensure directory exists
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                $this->logger->log("Failed to create directory: $dir", "ERROR");
                throw new RuntimeException("Failed to create directory: $dir");
            }
        }
        
        $content = implode(PHP_EOL, $lines);
        $result = file_put_contents($filePath, $content);
        
        if ($result === false) {
            $this->logger->log("Error: Failed to write to file: $filePath", "ERROR");
            throw new RuntimeException("Failed to write to file: $filePath");
        }
        
        $this->logger->log("Successfully wrote " . count($lines) . " lines to $filePath", "INFO");
        return true;
    }
} 