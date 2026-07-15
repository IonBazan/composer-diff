<?php

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Diff\DiffEntries;

interface Formatter
{
    public function render(DiffEntries $prodEntries, DiffEntries $devEntries, bool $withUrls, bool $withLicenses): void;

    public function renderSingle(DiffEntries $entries, string $title, bool $withUrls, bool $withLicenses): void;
}
