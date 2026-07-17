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

    /**
     * Returns a new collection sorted by package name or operation type.
     *
     * @param string $by 'name' or 'operation'
     */
    public function sorted(string $by = 'name'): self
    {
        $entries = $this->getArrayCopy();

        if ('operation' === $by) {
            $order = [
                DiffEntry::TYPE_INSTALL => 0,
                DiffEntry::TYPE_UPGRADE => 1,
                DiffEntry::TYPE_DOWNGRADE => 2,
                DiffEntry::TYPE_CHANGE => 3,
                DiffEntry::TYPE_REMOVE => 4,
            ];

            usort($entries, static function (DiffEntry $a, DiffEntry $b) use ($order): int {
                $cmp = ($order[$a->getType()] ?? 99) - ($order[$b->getType()] ?? 99);
                if (0 !== $cmp) {
                    return $cmp;
                }

                return strcmp($a->getPackageName(), $b->getPackageName());
            });
        } else {
            usort($entries, static function (DiffEntry $a, DiffEntry $b): int {
                return strcmp($a->getPackageName(), $b->getPackageName());
            });
        }

        return new self($entries);
    }
}
