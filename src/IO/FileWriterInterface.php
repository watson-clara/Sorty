<?php
declare(strict_types=1);

namespace src\IO;

use src\Exceptions\FileException;

/**
 * Interface for writing to files
 */
interface FileWriterInterface
{
    /**
     * Write an array of lines to a file
     *
     * @param string $filePath Path to the file to write
     * @param array $lines Array of lines to write
     * @return bool True if the write was successful
     * @throws FileException If the file cannot be written
     */
    public function writeLines(string $filePath, array $lines): bool;
} 