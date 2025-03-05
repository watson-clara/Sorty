<?php
declare(strict_types=1);

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use src\Services\ComparisonService;
use src\Comparers\ComparerInterface;
use src\IO\FileReaderInterface;
use src\IO\FileWriterInterface;
use src\Logging\LoggerInterface;

class ComparisonServiceTest extends TestCase
{
    private ComparisonService $service;
    private FileReaderInterface $fileReader;
    private FileWriterInterface $fileWriter;
    private ComparerInterface $comparer;
    private LoggerInterface $logger;
    
    protected function setUp(): void
    {
        // Create mocks
        $this->fileReader = $this->createMock(FileReaderInterface::class);
        $this->fileWriter = $this->createMock(FileWriterInterface::class);
        $this->comparer = $this->createMock(ComparerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        
        // Create service
        $this->service = new ComparisonService(
            $this->fileReader,
            $this->fileWriter,
            $this->comparer,
            $this->logger
        );
    }
    
    public function testCompareFiles(): void
    {
        // Test data
        $inputFile1 = "input1.txt";
        $inputFile2 = "input2.txt";
        $outputFile1 = "output1.txt";
        $outputFile2 = "output2.txt";
        
        $lines1 = ["apple", "banana", "cherry"];
        $lines2 = ["banana", "date", "fig"];
        
        $uniqueLines1 = ["apple", "cherry"];
        $uniqueLines2 = ["date", "fig"];
        
        // Set up mock expectations
        $this->fileReader->expects($this->exactly(2))
            ->method('readLines')
            ->willReturnMap([
                [$inputFile1, 10485760, $lines1],
                [$inputFile2, 10485760, $lines2]
            ]);
            
        $this->comparer->expects($this->once())
            ->method('compare')
            ->with($this->equalTo($lines1), $this->equalTo($lines2))
            ->willReturn([$uniqueLines1, $uniqueLines2]);
        
        // Replace withConsecutive with separate expects calls
        $this->fileWriter->expects($this->exactly(2))
            ->method('writeLines')
            ->willReturn(true);
            
        $this->logger->expects($this->exactly(2))
            ->method('log')
            ->willReturn(true);
        
        // Call the method
        $result = $this->service->compareFiles($inputFile1, $inputFile2, $outputFile1, $outputFile2);
        
        // Assert the result
        $this->assertEquals([
            'uniqueLines1' => 2,
            'uniqueLines2' => 2,
            'totalLines1' => 3,
            'totalLines2' => 3
        ], $result);
    }
    
    public function testCompareLines(): void
    {
        // Test data
        $lines1 = ["apple", "banana", "cherry"];
        $lines2 = ["banana", "date", "fig"];
        
        $uniqueLines1 = ["apple", "cherry"];
        $uniqueLines2 = ["date", "fig"];
        
        // Set up mock expectations
        $this->comparer->expects($this->once())
            ->method('compare')
            ->with($lines1, $lines2)
            ->willReturn([$uniqueLines1, $uniqueLines2]);
            
        $this->logger->expects($this->once())
            ->method('log')
            ->willReturn(true);
        
        // Call the method
        $result = $this->service->compareLines($lines1, $lines2);
        
        // Assert the result
        $this->assertEquals([$uniqueLines1, $uniqueLines2], $result);
    }
}