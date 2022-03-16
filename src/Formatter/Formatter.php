<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;

interface Formatter
{
    public function render(DiffEntries $prodEntries, DiffEntries $devEntries, bool $withUrls): void;

    public function renderSingle(DiffEntries $entries, string $title, bool $withUrls): void;

    public function getUrl(DiffEntry $entry): ?string;
}
