<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use src\Logging\FileLogger;

// Instead, use the proper namespace and class imports
use src\Comparers\LineByLineComparer;
use src\Services\ComparisonService;
use src\IO\FileReader;
use src\IO\FileWriter;
use RuntimeException;

class CLITest extends TestCase
{
    private string $inputFile1;
    private string $inputFile2;
    private string $outputFile1;
    private string $outputFile2;
    private FileLogger $logger;

    protected function setUp(): void
    {
        // Create temporary files for testing
        $this->inputFile1 = tempnam(sys_get_temp_dir(), 'input1_');
        $this->inputFile2 = tempnam(sys_get_temp_dir(), 'input2_');
        $this->outputFile1 = tempnam(sys_get_temp_dir(), 'output1_');
        $this->outputFile2 = tempnam(sys_get_temp_dir(), 'output2_');
        $this->logger = new FileLogger();
    }

    protected function tearDown(): void
    {
        // Clean up temporary files
        if (file_exists($this->inputFile1)) unlink($this->inputFile1);
        if (file_exists($this->inputFile2)) unlink($this->inputFile2);
        if (file_exists($this->outputFile1)) unlink($this->outputFile1);
        if (file_exists($this->outputFile2)) unlink($this->outputFile2);
    }

    public function testBasicComparison(): void
    {
        // Create sorted test data
        $lines1 = ["apple", "banana", "cherry", "date", "fig"];
        $lines2 = ["banana", "date", "grape", "kiwi", "lemon"];

        // Write lines to input files
        file_put_contents($this->inputFile1, implode("\n", $lines1));
        file_put_contents($this->inputFile2, implode("\n", $lines2));

        $fileReader = new FileReader($this->logger);
        $fileWriter = new FileWriter($this->logger);
        $lineComparer = new LineByLineComparer();
        $service = new ComparisonService($fileReader, $fileWriter, $lineComparer, $this->logger);

        // Then use the service to compare files
        $service->compareFiles($this->inputFile1, $this->inputFile2, $this->outputFile1, $this->outputFile2);

        // Expected unique lines
        $expectedOutput1 = ["apple", "cherry", "fig"];
        $expectedOutput2 = ["grape", "kiwi", "lemon"];

        // Read actual output
        $actualOutput1 = file($this->outputFile1, FILE_IGNORE_NEW_LINES);
        $actualOutput2 = file($this->outputFile2, FILE_IGNORE_NEW_LINES);

        // Assert output files match expected output
        $this->assertEquals($expectedOutput1, $actualOutput1);
        $this->assertEquals($expectedOutput2, $actualOutput2);
    }

    public function testLargerFiles(): void
    {
        // Generate larger sorted datasets
        $lines1 = [];
        $lines2 = [];
        
        // Create 5000 lines for file 1 (even numbers)
        for ($i = 0; $i < 10000; $i += 2) {
            $lines1[] = "line" . str_pad($i, 6, '0', STR_PAD_LEFT);
        }
        
        // Create 5000 lines for file 2 (odd numbers)
        for ($i = 1; $i < 10000; $i += 2) {
            $lines2[] = "line" . str_pad($i, 6, '0', STR_PAD_LEFT);
        }

        // Write lines to input files
        file_put_contents($this->inputFile1, implode("\n", $lines1));
        file_put_contents($this->inputFile2, implode("\n", $lines2));

        $fileReader = new FileReader($this->logger);
        $fileWriter = new FileWriter($this->logger);
        $lineComparer = new LineByLineComparer();
        $service = new ComparisonService($fileReader, $fileWriter, $lineComparer, $this->logger);

        // Then use the service to compare files
        $service->compareFiles($this->inputFile1, $this->inputFile2, $this->outputFile1, $this->outputFile2);

        // Read actual output
        $actualOutput1 = file($this->outputFile1, FILE_IGNORE_NEW_LINES);
        $actualOutput2 = file($this->outputFile2, FILE_IGNORE_NEW_LINES);

        // Assert output files have the correct number of lines
        $this->assertCount(5000, $actualOutput1);
        $this->assertCount(5000, $actualOutput2);
        
        // Check a few sample lines
        $this->assertContains("line000000", $actualOutput1);
        $this->assertContains("line009998", $actualOutput1);
        $this->assertContains("line000001", $actualOutput2);
        $this->assertContains("line009999", $actualOutput2);
    }

    public function testPartiallyOverlappingFiles(): void
    {
        // Simulate partially overlapping files
        $lines1 = array_map(fn($i) => "line" . str_pad($i, 4, '0', STR_PAD_LEFT), range(1, 1000));  // lines 1-1000
        $lines2 = array_map(fn($i) => "line" . str_pad($i, 4, '0', STR_PAD_LEFT), range(500, 1500));  // lines 500-1500

        // Write lines to input files
        file_put_contents($this->inputFile1, implode("\n", $lines1));
        file_put_contents($this->inputFile2, implode("\n", $lines2));

        $fileReader = new FileReader($this->logger);
        $fileWriter = new FileWriter($this->logger);
        $lineComparer = new LineByLineComparer();
        $service = new ComparisonService($fileReader, $fileWriter, $lineComparer, $this->logger);

        // Then use the service to compare files
        $service->compareFiles($this->inputFile1, $this->inputFile2, $this->outputFile1, $this->outputFile2);

        // Read expected output
        $expectedOutput1 = array_map(fn($i) => "line" . str_pad($i, 4, '0', STR_PAD_LEFT), range(1, 499)); // lines 1 to 499
        $expectedOutput2 = array_map(fn($i) => "line" . str_pad($i, 4, '0', STR_PAD_LEFT), range(1001, 1500)); // lines 1001 to 1500

        // Read actual output
        $actualOutput1 = file($this->outputFile1, FILE_IGNORE_NEW_LINES);
        $actualOutput2 = file($this->outputFile2, FILE_IGNORE_NEW_LINES);

        // Assert output files match expected output
        $this->assertEquals($expectedOutput1, $actualOutput1);
        $this->assertEquals($expectedOutput2, $actualOutput2);
    }

    public function testEmptyFiles(): void
    {
        // Create empty files
        file_put_contents($this->inputFile1, "");
        file_put_contents($this->inputFile2, "");

        $fileReader = new FileReader($this->logger);
        $fileWriter = new FileWriter($this->logger);
        $lineComparer = new LineByLineComparer();
        $service = new ComparisonService($fileReader, $fileWriter, $lineComparer, $this->logger);

        // Then use the service to compare files
        $service->compareFiles($this->inputFile1, $this->inputFile2, $this->outputFile1, $this->outputFile2);

        // Assert output files are empty
        $this->assertEmpty(file_get_contents($this->outputFile1));
        $this->assertEmpty(file_get_contents($this->outputFile2));
    }

    public function testOneEmptyFile(): void
    {
        // One file has content, the other is empty
        $lines = ["apple", "banana", "cherry"];
        file_put_contents($this->inputFile1, implode("\n", $lines));
        file_put_contents($this->inputFile2, "");

        $fileReader = new FileReader($this->logger);
        $fileWriter = new FileWriter($this->logger);
        $lineComparer = new LineByLineComparer();
        $service = new ComparisonService($fileReader, $fileWriter, $lineComparer, $this->logger);

        // Then use the service to compare files
        $service->compareFiles($this->inputFile1, $this->inputFile2, $this->outputFile1, $this->outputFile2);

        // Assert all lines from file1 are in output1, and output2 is empty
        $actualOutput1 = file($this->outputFile1, FILE_IGNORE_NEW_LINES);
        $this->assertEquals($lines, $actualOutput1);
        $this->assertEmpty(file_get_contents($this->outputFile2));
    }

    public function testFileTooLarge(): void
    {
        // Create a mock FileReader that throws an exception for large files
        $mockReader = $this->createMock(FileReader::class);
        $mockReader->method('readLines')
            ->willThrowException(new \RuntimeException("One or both files are too large"));
        
        // Create a file with some content
        $largeContent = str_repeat("line\n", 50);
        file_put_contents($this->inputFile1, $largeContent);
        file_put_contents($this->inputFile2, "small content");

        // Create service with the mock reader
        $fileWriter = new FileWriter($this->logger);
        $lineComparer = new LineByLineComparer();
        $service = new ComparisonService($mockReader, $fileWriter, $lineComparer, $this->logger);

        // Assert that an exception is thrown when the service tries to read the files
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("One or both files are too large");

        // This should throw the exception
        $service->compareFiles($this->inputFile1, $this->inputFile2, $this->outputFile1, $this->outputFile2);
    }

    public function testCliExecution(): void
    {
        // Create test files with content
        $lines1 = ["apple", "banana", "cherry"];
        $lines2 = ["banana", "date", "fig"];
        file_put_contents($this->inputFile1, implode("\n", $lines1));
        file_put_contents($this->inputFile2, implode("\n", $lines2));
        
        // Expected unique lines
        $expectedUnique1 = ["apple", "cherry"];
        $expectedUnique2 = ["date", "fig"];
        
        // Create the components needed for CLI execution
        $fileReader = new FileReader($this->logger);
        $fileWriter = new FileWriter($this->logger);
        $lineComparer = new LineByLineComparer();
        $service = new ComparisonService($fileReader, $fileWriter, $lineComparer, $this->logger);
        
        // Execute the comparison (simulating CLI execution)
        $result = $service->compareFiles($this->inputFile1, $this->inputFile2, $this->outputFile1, $this->outputFile2);
        
        // Verify the results
        $this->assertEquals(2, $result['uniqueLines1']);
        $this->assertEquals(2, $result['uniqueLines2']);
        $this->assertEquals(3, $result['totalLines1']);
        $this->assertEquals(3, $result['totalLines2']);
        
        // Verify the output files
        $actualOutput1 = file($this->outputFile1, FILE_IGNORE_NEW_LINES);
        $actualOutput2 = file($this->outputFile2, FILE_IGNORE_NEW_LINES);
        $this->assertEquals($expectedUnique1, $actualOutput1);
        $this->assertEquals($expectedUnique2, $actualOutput2);
    }
}
