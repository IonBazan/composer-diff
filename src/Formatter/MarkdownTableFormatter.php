<?php

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Formatter\Helper\Table;

class MarkdownTableFormatter extends MarkdownFormatter
{
    /**
     * {@inheritdoc}
     */
    public function render(DiffEntries $prodEntries, DiffEntries $devEntries, $withUrls, $withLicenses)
    {
        $this->renderSingle($prodEntries, 'Prod Packages', $withUrls, $withLicenses);
        $this->renderSingle($devEntries, 'Dev Packages', $withUrls, $withLicenses);
    }

    /**
     * {@inheritdoc}
     */
    public function renderSingle(DiffEntries $entries, $title, $withUrls, $withLicenses)
    {
        if (!\count($entries)) {
            return;
        }

        $rows = array();

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
        $headers = array($title, 'Operation', 'Base', 'Target');

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
     * @param bool $withUrls
     *
     * @return string[]
     */
    private function getTableRow(DiffEntry $entry, $withUrls)
    {
        $packageName = $this->getDecoratedPackageName($entry);
        $packageUrl = $withUrls ? $this->formatUrl($entry->getProjectUrl(), $packageName) : $packageName;

        if ($entry->isInstall()) {
            return array(
                $packageUrl ?: $packageName,
                '<fg=green>New</>',
                '-',
                $entry->getTargetVersion(),
            );
        }

        if ($entry->isRemove()) {
            return array(
                $packageUrl ?: $packageName,
                '<fg=red>Removed</>',
                $entry->getBaseVersion(),
                '-',
            );
        }

        return array(
            $packageUrl ?: $packageName,
            $entry->isChange() ? '<fg=magenta>Changed</>' : ($entry->isUpgrade() ? '<fg=cyan>Upgraded</>' : '<fg=yellow>Downgraded</>'),
            $entry->getBaseVersion(),
            $entry->getTargetVersion(),
        );
    }
}
