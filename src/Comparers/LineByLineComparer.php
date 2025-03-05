<?php
declare(strict_types=1);

namespace src\Comparers;

/**
 * Compares two collections line by line
 * 
 * Assumes both collections are lexicographically sorted
 */
class LineByLineComparer implements ComparerInterface
{
    /**
     * Compare two sorted collections and find unique elements in each
     *
     * @param array $collection1 First collection (must be sorted)
     * @param array $collection2 Second collection (must be sorted)
     * @return array Array with two sub-arrays containing unique elements
     */
    public function compare(array $collection1, array $collection2): array
    {
        $unique1 = [];
        $unique2 = [];
        
        $i = 0;
        $j = 0;
        
        while ($i < count($collection1) || $j < count($collection2)) {
            // Case 1: End of collection1 reached
            if ($i >= count($collection1)) {
                $unique2[] = $collection2[$j];
                $j++;
                continue;
            }
            
            // Case 2: End of collection2 reached
            if ($j >= count($collection2)) {
                $unique1[] = $collection1[$i];
                $i++;
                continue;
            }
            
            // Case 3: Compare the current elements
            $comparison = strcmp($collection1[$i], $collection2[$j]);
            
            if ($comparison < 0) {
                // Element in collection1 is smaller, it's unique
                $unique1[] = $collection1[$i];
                $i++;
            } elseif ($comparison > 0) {
                // Element in collection2 is smaller, it's unique
                $unique2[] = $collection2[$j];
                $j++;
            } else {
                // Elements are identical, skip both
                $i++;
                $j++;
            }
        }
        
        return [$unique1, $unique2];
    }
} 