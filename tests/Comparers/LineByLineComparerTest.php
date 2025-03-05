<?php
declare(strict_types=1);

namespace Tests\Comparers;

use PHPUnit\Framework\TestCase;
use src\Comparers\LineByLineComparer;

class LineByLineComparerTest extends TestCase
{
    private LineByLineComparer $comparer;
    
    protected function setUp(): void
    {
        $this->comparer = new LineByLineComparer();
    }
    
    public function testBasicComparison(): void
    {
        $lines1 = ["apple", "banana", "cherry", "date", "fig"];
        $lines2 = ["banana", "date", "grape", "kiwi", "lemon"];
        
        list($unique1, $unique2) = $this->comparer->compare($lines1, $lines2);
        
        $this->assertEquals(["apple", "cherry", "fig"], $unique1);
        $this->assertEquals(["grape", "kiwi", "lemon"], $unique2);
    }
    
    public function testEmptyArrays(): void
    {
        $lines1 = [];
        $lines2 = [];
        
        list($unique1, $unique2) = $this->comparer->compare($lines1, $lines2);
        
        $this->assertEmpty($unique1);
        $this->assertEmpty($unique2);
    }
    
    public function testOneEmptyArray(): void
    {
        $lines1 = ["apple", "banana", "cherry"];
        $lines2 = [];
        
        list($unique1, $unique2) = $this->comparer->compare($lines1, $lines2);
        
        $this->assertEquals($lines1, $unique1);
        $this->assertEmpty($unique2);
    }
    
    public function testIdenticalArrays(): void
    {
        $lines1 = ["apple", "banana", "cherry"];
        $lines2 = ["apple", "banana", "cherry"];
        
        list($unique1, $unique2) = $this->comparer->compare($lines1, $lines2);
        
        $this->assertEmpty($unique1);
        $this->assertEmpty($unique2);
    }
    
    public function testPartiallyOverlappingArrays(): void
    {
        $lines1 = ["apple", "cherry", "date"];
        $lines2 = ["banana", "cherry", "date", "fig"];
        
        list($unique1, $unique2) = $this->comparer->compare($lines1, $lines2);
        
        $this->assertEquals(["apple"], $unique1);
        $this->assertEquals(["banana", "fig"], $unique2);
    }
}