<?php
declare(strict_types=1);

namespace src\IO;

use src\Logging\LoggerInterface;
use RuntimeException;

/**
 * Reads files and returns their contents as arrays of lines
 */
class FileReader implements FileReaderInterface
{
    private LoggerInterface $logger;
    private int $maxFileSize;
    
    /**
     * Constructor
     *
     * @param LoggerInterface $logger Logger for recording operations
     * @param int $maxFileSize Maximum allowed file size in bytes
     */
    public function __construct(LoggerInterface $logger, int $maxFileSize = 10485760)
    {
        $this->logger = $logger;
        $this->maxFileSize = $maxFileSize;
    }
    
    /**
     * Read a file and return its contents as an array of lines
     *
     * @param string $filePath Path to the file to read
     * @param int|null $maxFileSize Maximum allowed file size in bytes
     * @return array Array of lines from the file
     * @throws RuntimeException If the file cannot be read or is too large
     */
    public function readLines(string $filePath, ?int $maxFileSize = null): array
    {
        if (!file_exists($filePath)) {
            $this->logger->log("Error: File not found: $filePath", "ERROR");
            throw new RuntimeException("File not found: $filePath");
        }
        
        $maxSize = $maxFileSize ?? $this->maxFileSize;
        $fileSize = filesize($filePath);
        
        if ($fileSize > $maxSize) {
            $this->logger->log("Error: File too large: $filePath ($fileSize bytes)", "ERROR");
            throw new RuntimeException("One or both files are too large");
        }
        
        // Read file content and split into lines
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        if ($lines === false) {
            $this->logger->log("Error: Could not read file: $filePath", "ERROR");
            throw new RuntimeException("Could not read file: $filePath");
        }
        
        $this->logger->log("Successfully read " . count($lines) . " lines from $filePath", "INFO");
        return $lines;
    }
} 