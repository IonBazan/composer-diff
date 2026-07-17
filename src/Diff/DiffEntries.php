<?php

namespace IonBazan\ComposerDiff\Diff;

use ArrayIterator;
use Composer\Package\BasePackage;
use Composer\Pcre\Preg;

/**
 * @extends ArrayIterator<int, DiffEntry>
 */
class DiffEntries extends ArrayIterator
{
    /**
     * Returns a new collection containing only entries whose package name matches at least one of the given glob patterns.
     *
     * @param string[] $patterns
     */
    public function matching(array $patterns): self
    {
        $regexp = BasePackage::packageNamesToRegexp($patterns);
        $filtered = [];

        /** @var DiffEntry $entry */
        foreach ($this as $entry) {
            if (Preg::isMatch($regexp, $entry->getPackageName())) {
                $filtered[] = $entry;
            }
        }

        return new self($filtered);
    }
}
