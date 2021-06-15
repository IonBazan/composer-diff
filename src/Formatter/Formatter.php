<?php

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;

interface Formatter
{
    /**
     * @param bool $withUrls
     *
     * @return void
     */
    public function render(DiffEntries $prodEntries, DiffEntries $devEntries, $withUrls);

    /**
     * @param string $title
     * @param bool   $withUrls
     *
     * @return void
     */
    public function renderSingle(DiffEntries $entries, $title, $withUrls);

    /**
     * @return string|null
     */
    public function getUrl(DiffEntry $entry);
}
