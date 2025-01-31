<?php

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;

interface Formatter
{
    /**
     * @param bool $withUrls
     * @param bool $withLicenses
     *
     * @return void
     */
    public function render(DiffEntries $prodEntries, DiffEntries $devEntries, $withUrls, $withLicenses);

    /**
     * @param string $title
     * @param bool   $withUrls
     * @param bool   $withLicenses
     *
     * @return void
     */
    public function renderSingle(DiffEntries $entries, $title, $withUrls, $withLicenses);

    /**
     * @return string|null
     */
    public function getUrl(DiffEntry $entry);

    /**
     * @return string|null
     */
    public function getLicenses(DiffEntry $entry);
}
