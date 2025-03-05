<?php
declare(strict_types=1);

/**
 * FileComparer - A utility for comparing two lexicographically sorted text files
 * 
 * This class identifies unique lines in each file and outputs them to separate files.
 * It's designed to handle large files efficiently by processing them line by line.
 */
class FileComparer
{
    private string $inputFile1;
    private string $inputFile2;
    private string $outputFile1;
    private string $outputFile2;
    private int $maxFileSize;
    private Logger $logger;

    /**
     * Constructor
     * 
     * @param string $inputFile1 Path to the first input file
     * @param string $inputFile2 Path to the second input file
     * @param string $outputFile1 Path to the first output file
     * @param string $outputFile2 Path to the second output file
     * @param Logger $logger Logger instance
     * @param int $maxFileSize Maximum allowed file size in bytes
     */
    public function __construct(string $inputFile1, string $inputFile2, string $outputFile1, string $outputFile2, Logger $logger, int $maxFileSize = 10485760)
    {
        $this->inputFile1 = $inputFile1;
        $this->inputFile2 = $inputFile2;
        $this->outputFile1 = $outputFile1;
        $this->outputFile2 = $outputFile2;
        $this->maxFileSize = $maxFileSize; // 10 MB by default
        $this->logger = $logger;
    }

    /**
     * Compares two files and writes unique lines to separate output files.
     * 
     * @throws RuntimeException If files are too large or cannot be opened
     */
    public function compare(): void
    {
        $file1Size = filesize($this->inputFile1);
        $file2Size = filesize($this->inputFile2);

        // Check if file size exceeds limit
        if ($file1Size > $this->maxFileSize || $file2Size > $this->maxFileSize) {
            $this->logger->log("One or both files are too large. Maximum allowed size is " . ($this->maxFileSize / 1048576) . " MB.");
            throw new RuntimeException("One or both files are too large.");
        }

        // Open the files safely using try-catch-finally for proper resource management
        try {
            $file1Handle = fopen($this->inputFile1, 'r');
            $file2Handle = fopen($this->inputFile2, 'r');
            $output1Handle = fopen($this->outputFile1, 'w');
            $output2Handle = fopen($this->outputFile2, 'w');

            if (!$file1Handle || !$file2Handle || !$output1Handle || !$output2Handle) {
                throw new RuntimeException("Failed to open one of the files.");
            }

            // Log that file reading is starting
            $this->logger->log("Starting to compare files: {$this->inputFile1} and {$this->inputFile2}");

            // Read initial lines
            $line1 = $this->readNextLine($file1Handle);
            $line2 = $this->readNextLine($file2Handle);

            // Process until we reach the end of both files
            while ($line1 !== null || $line2 !== null) {
                // Case 1: End of file1 reached, write remaining lines from file2
                if ($line1 === null) {
                    fwrite($output2Handle, $line2 . PHP_EOL);
                    $line2 = $this->readNextLine($file2Handle);
                    continue;
                }

                // Case 2: End of file2 reached, write remaining lines from file1
                if ($line2 === null) {
                    fwrite($output1Handle, $line1 . PHP_EOL);
                    $line1 = $this->readNextLine($file1Handle);
                    continue;
                }

                // Case 3: Compare the current lines
                $comparison = strcmp($line1, $line2);

                if ($comparison < 0) {
                    // Line1 is lexicographically smaller, it's unique to file1
                    fwrite($output1Handle, $line1 . PHP_EOL);
                    $line1 = $this->readNextLine($file1Handle);
                } elseif ($comparison > 0) {
                    // Line2 is lexicographically smaller, it's unique to file2
                    fwrite($output2Handle, $line2 . PHP_EOL);
                    $line2 = $this->readNextLine($file2Handle);
                } else {
                    // Lines are identical, skip both
                    $line1 = $this->readNextLine($file1Handle);
                    $line2 = $this->readNextLine($file2Handle);
                }
            }

            // Log that the comparison is complete
            $this->logger->log("File comparison complete.");

        } finally {
            // Ensure all file handles are closed
            if (isset($file1Handle)) fclose($file1Handle);
            if (isset($file2Handle)) fclose($file2Handle);
            if (isset($output1Handle)) fclose($output1Handle);
            if (isset($output2Handle)) fclose($output2Handle);
        }
    }

    /**
     * Reads the next non-empty line from a file.
     * 
     * @param resource $fileHandle File handle to read from
     * @return string|null Returns null if end of file is reached
     */
    private function readNextLine($fileHandle): ?string
    {
        while (($line = fgets($fileHandle)) !== false) {
            $line = trim($line);
            if ($line !== '') {
                return $line;
            }
        }
        return null;
    }
}
