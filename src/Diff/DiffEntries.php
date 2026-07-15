<?php

namespace IonBazan\ComposerDiff\Diff;

use ArrayIterator;

/**
 * @extends ArrayIterator<int, DiffEntry>
 */
class DiffEntries extends ArrayIterator
{
    /**
     * @param array<int, DiffEntry> $array
     */
    public function __construct(array $array = [], int $flags = 0)
    {
        parent::__construct($array, $flags);
    }
}
