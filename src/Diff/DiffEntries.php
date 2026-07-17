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
            $order = array_flip([
                DiffEntry::TYPE_INSTALL,
                DiffEntry::TYPE_UPGRADE,
                DiffEntry::TYPE_DOWNGRADE,
                DiffEntry::TYPE_CHANGE,
                DiffEntry::TYPE_REMOVE,
            ]);

            usort($entries, static function (DiffEntry $a, DiffEntry $b) use ($order): int {
                $cmp = $order[$a->getType()] - $order[$b->getType()];
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
