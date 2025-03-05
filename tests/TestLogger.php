<?php
namespace Tests;

use src\Logging\LoggerInterface;

class TestLogger implements LoggerInterface
{
    public function log($message, $level = 'INFO'): bool
    {
        // Do nothing in tests
        return true;
    }
} 