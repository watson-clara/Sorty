<?php
declare(strict_types=1);

namespace Tests\IO;

use PHPUnit\Framework\TestCase;
use src\IO\FileWriter;
use src\Logging\LoggerInterface;
use src\Exceptions\FileException;

class FileWriterTest extends TestCase
{
    private FileWriter $fileWriter;
    private LoggerInterface $logger;
    private string $testDir;
    private string $testFile;
    
    protected function setUp(): void
    {
        // Create mock logger
        $this->logger = $this->createMock(LoggerInterface::class);
        
        // Set up file writer
        $this->fileWriter = new FileWriter($this->logger);
        
        // Set up test directory and file
        $this->testDir = __DIR__ . '/../TestFiles';
        if (!is_dir($this->testDir)) {
            mkdir($this->testDir, 0755, true);
        }
        
        $this->testFile = $this->testDir . '/FileWriterTest.txt';
        
        // Ensure test file doesn't exist
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
    }
    
    protected function tearDown(): void
    {
        // Clean up test file
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
    }
    
    public function testWriteLinesToFile(): void
    {
        $lines = ["line1", "line2", "line3"];
        
        $result = $this->fileWriter->writeLines($this->testFile, $lines);
        
        $this->assertTrue($result);
        $this->assertFileExists($this->testFile);
        
        $content = file_get_contents($this->testFile);
        $this->assertEquals("line1" . PHP_EOL . "line2" . PHP_EOL . "line3", $content);
    }
    
    public function testWriteEmptyArrayToFile(): void
    {
        $lines = [];
        
        $result = $this->fileWriter->writeLines($this->testFile, $lines);
        
        $this->assertTrue($result);
        $this->assertFileExists($this->testFile);
        $this->assertEmpty(file_get_contents($this->testFile));
    }
    
    public function testWriteToNonExistentDirectory(): void
    {
        $lines = ["test"];
        $nonExistentDir = $this->testDir . '/NonExistent';
        $filePath = $nonExistentDir . '/test.txt';
        
        $result = $this->fileWriter->writeLines($filePath, $lines);
        
        $this->assertTrue($result);
        $this->assertFileExists($filePath);
        
        // Clean up
        unlink($filePath);
        rmdir($nonExistentDir);
    }
}