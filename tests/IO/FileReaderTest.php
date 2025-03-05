<?php
declare(strict_types=1);

namespace Tests\IO;

use PHPUnit\Framework\TestCase;
use src\IO\FileReader;
use src\Logging\LoggerInterface;
use RuntimeException;

class FileReaderTest extends TestCase
{
    private FileReader $fileReader;
    private LoggerInterface $logger;
    private string $testDir;
    private string $testFile;
    private string $largeFile;
    private string $emptyFile;
    
    protected function setUp(): void
    {
        // Create mock logger
        $this->logger = $this->createMock(LoggerInterface::class);
        
        // Set up file reader
        $this->fileReader = new FileReader($this->logger);
        
        // Set up test directory and files
        $this->testDir = __DIR__ . '/../TestFiles';
        if (!is_dir($this->testDir)) {
            mkdir($this->testDir, 0755, true);
        }
        
        $this->testFile = $this->testDir . '/FileReaderTest.txt';
        $this->largeFile = $this->testDir . '/LargeFileReaderTest.txt';
        $this->emptyFile = $this->testDir . '/EmptyFileReaderTest.txt';
        
        // Create test file with content
        file_put_contents($this->testFile, "line1\nline2\nline3\n");
        
        // Create empty file
        file_put_contents($this->emptyFile, "");
    }
    
    protected function tearDown(): void
    {
        // Clean up test files
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
        
        if (file_exists($this->largeFile)) {
            unlink($this->largeFile);
        }
        
        if (file_exists($this->emptyFile)) {
            unlink($this->emptyFile);
        }
    }
    
    public function testReadLinesFromFile(): void
    {
        $lines = $this->fileReader->readLines($this->testFile);
        
        $this->assertCount(3, $lines);
        $this->assertEquals("line1", $lines[0]);
        $this->assertEquals("line2", $lines[1]);
        $this->assertEquals("line3", $lines[2]);
    }
    
    public function testReadLinesFromEmptyFile(): void
    {
        $lines = $this->fileReader->readLines($this->emptyFile);
        
        $this->assertEmpty($lines);
    }
    
    public function testReadLinesFromNonExistentFile(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("File not found");
        
        $this->fileReader->readLines($this->testDir . '/NonExistentFile.txt');
    }
    
    public function testReadLinesFromFileTooLarge(): void
    {
        // Create a file larger than the max size
        $maxSize = 100; // 100 bytes
        $largeContent = str_repeat("x", 200); // 200 bytes
        file_put_contents($this->largeFile, $largeContent);
        
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("One or both files are too large");
        
        $this->fileReader->readLines($this->largeFile, $maxSize);
    }
    
    
}