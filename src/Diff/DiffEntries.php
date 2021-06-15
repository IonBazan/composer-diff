<?php

namespace IonBazan\ComposerDiff\Diff;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, DiffEntry>
 */
class DiffEntries implements IteratorAggregate, Countable
{
    /** @var DiffEntry[] */
    private $entries;

    /**
     * @param DiffEntry[] $entries
     */
    public function __construct(array $entries)
    {
        $this->entries = $entries;
    }

    /**
     * @return ArrayIterator<int, DiffEntry>
     */
    public function getIterator()
    {
        return new ArrayIterator($this->entries);
    }

    public function count()
    {
        return \count($this->entries);
    }
}
