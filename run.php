<?php
declare(strict_types=1);

// Replace autoloader with direct requires
require_once __DIR__ . '/src/Exceptions/FileException.php';
require_once __DIR__ . '/src/Logging/LoggerInterface.php';
require_once __DIR__ . '/src/Logging/FileLogger.php';
require_once __DIR__ . '/src/IO/FileReaderInterface.php';
require_once __DIR__ . '/src/IO/FileReader.php';
require_once __DIR__ . '/src/IO/FileWriterInterface.php';
require_once __DIR__ . '/src/IO/FileWriter.php';
require_once __DIR__ . '/src/Comparers/ComparerInterface.php';
require_once __DIR__ . '/src/Comparers/LineByLineComparer.php';
require_once __DIR__ . '/src/Services/ComparisonService.php';

use src\Comparers\LineByLineComparer;
use src\IO\FileReader;
use src\IO\FileWriter;
use src\Logging\FileLogger;
use src\Services\ComparisonService;
use src\Exceptions\FileException;

// Ensure correct usage: Require 4 arguments (input1, input2, output1, output2)
if ($argc < 5) {
    echo "Usage: php run.php <inputFile1> <inputFile2> <outputFile1> <outputFile2>\n";
    exit(1);
}

// Get file paths from arguments
$inputFile1 = $argv[1];
$inputFile2 = $argv[2];
$outputFile1 = $argv[3];
$outputFile2 = $argv[4];

// Check if input files exist
if (!file_exists($inputFile1) || !file_exists($inputFile2)) {
    echo "Error: One or both input files do not exist.\n";
    exit(1);
}

try {
    // Create dependencies
    $logger = new FileLogger();
    $fileReader = new FileReader($logger);
    $fileWriter = new FileWriter($logger);
    $comparer = new LineByLineComparer();
    
    // Create the service
    $service = new ComparisonService($fileReader, $fileWriter, $comparer, $logger);
    
    // Run comparison
    $stats = $service->compareFiles($inputFile1, $inputFile2, $outputFile1, $outputFile2);
    
    // Show results in console
    echo "\n--- Comparison Results ---\n";
    echo "Unique lines in $inputFile1: {$stats['uniqueLines1']}\n";
    echo "Unique lines in $inputFile2: {$stats['uniqueLines2']}\n";
    
    echo "\n--- Unique Lines in $inputFile1 ---\n";
    echo file_get_contents($outputFile1);
    
    echo "\n--- Unique Lines in $inputFile2 ---\n";
    echo file_get_contents($outputFile2);
    
    echo "\nResults saved to:\n";
    echo " - $outputFile1\n";
    echo " - $outputFile2\n";
    
} catch (FileException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Unexpected error: " . $e->getMessage() . "\n";
    exit(1);
}
