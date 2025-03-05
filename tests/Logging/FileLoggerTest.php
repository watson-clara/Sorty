<?php
declare(strict_types=1);

namespace Tests\Logging;

use PHPUnit\Framework\TestCase;
use src\Logging\FileLogger;

class FileLoggerTest extends TestCase
{
    private FileLogger $logger;
    private string $logFile;
    
    protected function setUp(): void
    {
        // Set up test log file
        $this->logFile = __DIR__ . '/../TestFiles/test.log';
        
        // Ensure directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Create logger with test log file
        $this->logger = new FileLogger($this->logFile);
        
        // Ensure log file doesn't exist
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }
    
    protected function tearDown(): void
    {
        // Clean up test log file
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }
    
    public function testLogMessage(): void
    {
        $result = $this->logger->log("Test message");
        
        $this->assertTrue($result);
        $this->assertFileExists($this->logFile);
        
        $content = file_get_contents($this->logFile);
        $this->assertStringContainsString("[INFO] Test message", $content);
    }
    
    public function testLogWithCustomLevel(): void
    {
        $this->logger->log("Error message", "ERROR");
        $this->logger->log("Warning message", "WARNING");
        
        $content = file_get_contents($this->logFile);
        $this->assertStringContainsString("[ERROR] Error message", $content);
        $this->assertStringContainsString("[WARNING] Warning message", $content);
    }
    
    public function testLogMultipleMessages(): void
    {
        $this->logger->log("Message 1");
        $this->logger->log("Message 2");
        $this->logger->log("Message 3");
        
        $content = file_get_contents($this->logFile);
        $lines = explode(PHP_EOL, trim($content));
        
        $this->assertCount(3, $lines);
        $this->assertStringContainsString("Message 1", $lines[0]);
        $this->assertStringContainsString("Message 2", $lines[1]);
        $this->assertStringContainsString("Message 3", $lines[2]);
    }
}