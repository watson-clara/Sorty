<?php
declare(strict_types=1);

namespace src\Comparers;

/**
 * Interface for comparing two collections of data
 */
interface ComparerInterface
{
    /**
     * Compare two collections and return unique elements from each
     *
     * @param array $collection1 First collection to compare
     * @param array $collection2 Second collection to compare
     * @return array Array with two sub-arrays containing unique elements
     */
    public function compare(array $collection1, array $collection2): array;
} 