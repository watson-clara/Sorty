<?php
declare(strict_types=1);

namespace src\Services;

use src\Comparers\ComparerInterface;
use src\IO\FileReaderInterface;
use src\IO\FileWriterInterface;
use src\Logging\LoggerInterface;
use src\Exceptions\FileException;

/**
 * Service for comparing files
 */
class ComparisonService
{
    private FileReaderInterface $fileReader;
    private FileWriterInterface $fileWriter;
    private ComparerInterface $comparer;
    private LoggerInterface $logger;
    private int $maxFileSize;
    
    /**
     * Constructor
     *
     * @param FileReaderInterface $fileReader File reader
     * @param FileWriterInterface $fileWriter File writer
     * @param ComparerInterface $comparer Comparer implementation
     * @param LoggerInterface $logger Logger
     * @param int $maxFileSize Maximum allowed file size in bytes
     */
    public function __construct(
        FileReaderInterface $fileReader,
        FileWriterInterface $fileWriter,
        ComparerInterface $comparer,
        LoggerInterface $logger,
        int $maxFileSize = 10485760
    ) {
        $this->fileReader = $fileReader;
        $this->fileWriter = $fileWriter;
        $this->comparer = $comparer;
        $this->logger = $logger;
        $this->maxFileSize = $maxFileSize;
    }
    
    /**
     * Compare two files and write unique lines to output files
     *
     * @param string $inputFile1 Path to first input file
     * @param string $inputFile2 Path to second input file
     * @param string $outputFile1 Path to first output file
     * @param string $outputFile2 Path to second output file
     * @return array Statistics about the comparison
     * @throws FileException If there's an error with file operations
     */
    public function compareFiles(
        string $inputFile1,
        string $inputFile2,
        string $outputFile1,
        string $outputFile2
    ): array {
        $this->logger->log("Starting file comparison...", "INFO");
        
        // Read input files
        $lines1 = $this->fileReader->readLines($inputFile1, $this->maxFileSize);
        $lines2 = $this->fileReader->readLines($inputFile2, $this->maxFileSize);
        
        // Compare the lines
        list($uniqueLines1, $uniqueLines2) = $this->comparer->compare($lines1, $lines2);
        
        // Write output files
        $this->fileWriter->writeLines($outputFile1, $uniqueLines1);
        $this->fileWriter->writeLines($outputFile2, $uniqueLines2);
        
        $this->logger->log("File comparison completed.", "INFO");
        
        // Return statistics
        return [
            'uniqueLines1' => count($uniqueLines1),
            'uniqueLines2' => count($uniqueLines2),
            'totalLines1' => count($lines1),
            'totalLines2' => count($lines2)
        ];
    }
    
    /**
     * Compare two arrays of lines and return unique lines from each
     *
     * @param array $lines1 First array of lines
     * @param array $lines2 Second array of lines
     * @return array Array with two sub-arrays containing unique lines
     */
    public function compareLines(array $lines1, array $lines2): array
    {
        $this->logger->log("Processing " . count($lines1) . " lines from file 1 and " . 
            count($lines2) . " lines from file 2", "INFO");
            
        return $this->comparer->compare($lines1, $lines2);
    }
} 