<?php

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Formatter\Helper\Table;

class MarkdownTableFormatter extends MarkdownFormatter
{
    public function render(DiffEntries $prodEntries, DiffEntries $devEntries, bool $withUrls, bool $withLicenses): void
    {
        $this->renderSingle($prodEntries, 'Prod Packages', $withUrls, $withLicenses);
        $this->renderSingle($devEntries, 'Dev Packages', $withUrls, $withLicenses);
    }

    public function renderSingle(DiffEntries $entries, string $title, bool $withUrls, bool $withLicenses): void
    {
        if (!\count($entries)) {
            return;
        }

        $rows = [];

        foreach ($entries as $entry) {
            $row = $this->getTableRow($entry, $withUrls);

            if ($withUrls) {
                $row[] = $this->formatUrl($entry->getUrl(), 'Compare');
            }

            if ($withLicenses) {
                $row[] = implode(', ', $entry->getLicenses());
            }

            $rows[] = $row;
        }

        $table = new Table($this->output);
        $headers = [$title, 'Operation', 'Base', 'Target'];

        if ($withUrls) {
            $headers[] = 'Link';
        }

        if ($withLicenses) {
            $headers[] = 'License';
        }

        $table->setHeaders($headers)->setRows($rows)->render();
        $this->output->writeln('');
    }

    /**
     * @return string[]
     */
    private function getTableRow(DiffEntry $entry, bool $withUrls): array
    {
        $packageName = $this->getDecoratedPackageName($entry);
        $packageUrl = $withUrls ? $this->formatUrl($entry->getProjectUrl(), $packageName) : $packageName;

        if ($entry->isInstall()) {
            return [
                $packageUrl ?: $packageName,
                '<fg=green>New</>',
                '-',
                $entry->getTargetVersion(),
            ];
        }

        if ($entry->isRemove()) {
            return [
                $packageUrl ?: $packageName,
                '<fg=red>Removed</>',
                $entry->getBaseVersion(),
                '-',
            ];
        }

        return [
            $packageUrl ?: $packageName,
            $entry->isChange() ? '<fg=magenta>Changed</>' : ($entry->isUpgrade() ? '<fg=cyan>Upgraded</>' : '<fg=yellow>Downgraded</>'),
            $entry->getBaseVersion(),
            $entry->getTargetVersion(),
        ];
    }
}
