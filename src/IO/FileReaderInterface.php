<?php
declare(strict_types=1);

namespace src\IO;

use src\Exceptions\FileException;

/**
 * Interface for reading files
 */
interface FileReaderInterface
{
    /**
     * Read a file and return its contents as an array of lines
     *
     * @param string $filePath Path to the file to read
     * @param int $maxFileSize Maximum allowed file size in bytes
     * @return array Array of lines from the file
     * @throws FileException If the file cannot be read or is too large
     */
    public function readLines(string $filePath, int $maxFileSize = 10485760): array;
} 