<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Diff\DiffEntries;
use Symfony\Component\Console\Helper\Table;

class CliFormatter extends MarkdownTableFormatter
{
    public function render(DiffEntries $prodEntries, DiffEntries $devEntries, bool $withUrls): void
    {
        $this->renderSingle($prodEntries, 'Prod Packages', $withUrls);
        $this->renderSingle($devEntries, 'Dev Packages', $withUrls);
    }

    public function renderSingle(DiffEntries $entries, string $title, bool $withUrls): void
    {
        if (!\count($entries)) {
            return;
        }

        $rows = [];

        foreach ($entries as $entry) {
            $row = $this->getTableRow($entry, $withUrls);

            if ($withUrls) {
                $row[] = $this->formatUrl($this->getUrl($entry), 'Compare');
            }

            $rows[] = $row;
        }

        $table = new Table($this->output);
        $headers = [$title, 'Operation', 'Base', 'Target'];

        if ($withUrls) {
            $headers[] = 'Link';
        }

        $table->setHeaders($headers)->setRows($rows)->render();
        $this->output->writeln('');
    }

    protected function formatUrl(?string $url, string $title): string
    {
        return null !== $url ? sprintf('<href=%s>%s</>', $url, $title) : '';
    }
}
