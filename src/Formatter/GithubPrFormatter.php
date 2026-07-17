<?php

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Diff\DiffEntries;
use function count;

class GithubPrFormatter extends MarkdownTableFormatter
{
    public function renderSingle(DiffEntries $entries, string $title, bool $withUrls, bool $withLicenses): void
    {
        if (!count($entries)) {
            return;
        }

        $this->output->writeln('<details>');
        $this->output->writeln(sprintf('<summary>%s (%d packages)</summary>', $title, count($entries)));
        $this->output->writeln('');
        parent::renderSingle($entries, $title, $withUrls, $withLicenses);
        $this->output->writeln('</details>');
        $this->output->writeln('');
    }
}
