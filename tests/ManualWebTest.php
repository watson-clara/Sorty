<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

class ManualWebTest extends TestCase
{
    public function testManualInstructions(): void
    {
        // This is just a placeholder test that always passes
        // It's meant to remind users to manually test the web interface
        $this->assertTrue(true, 'Please follow the manual testing instructions for the web interface');
    }
}

/**
 * ManualWebTest - Helper script for manual testing of the web interface
 * 
 * This script creates test files with known content for manual testing.
 */

// 1. Create test files
$testDir = __DIR__ . '/TestFiles';
if (!is_dir($testDir)) {
    mkdir($testDir, 0755, true);
}

// Create file 1 with sorted content
$file1 = $testDir . '/File1.txt';
$lines1 = ["apple", "banana", "cherry", "date", "fig"];
file_put_contents($file1, implode("\n", $lines1));
echo "Created test file 1: $file1\n";

// Create file 2 with sorted content
$file2 = $testDir . '/File2.txt';
$lines2 = ["banana", "date", "grape", "kiwi", "lemon"];
file_put_contents($file2, implode("\n", $lines2));
echo "Created test file 2: $file2\n";

// Create larger test files
$file3 = $testDir . '/Large1.txt';
$file4 = $testDir . '/Large2.txt';

$largeLines1 = [];
$largeLines2 = [];

// Create 5000 lines for file 1 (even numbers)
for ($i = 0; $i < 10000; $i += 2) {
    $largeLines1[] = "line" . str_pad($i, 6, '0', STR_PAD_LEFT);
}

// Create 5000 lines for file 2 (odd numbers)
for ($i = 1; $i < 10000; $i += 2) {
    $largeLines2[] = "line" . str_pad($i, 6, '0', STR_PAD_LEFT);
}

file_put_contents($file3, implode("\n", $largeLines1));
file_put_contents($file4, implode("\n", $largeLines2));

echo "Created large test file 1: $file3\n";
echo "Created large test file 2: $file4\n";

echo "\nManual Testing Instructions:\n";
echo "1. Start the PHP development server: php -S localhost:8000 -t public\n";
echo "2. Open http://localhost:8000 in your browser\n";
echo "3. Upload the test files created in $testDir\n";
echo "4. Verify the comparison results\n";
echo "5. Test downloading the results\n";
echo "6. Test clearing the results\n"; 