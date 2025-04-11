<?php

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Diff\DiffEntries;

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
}
