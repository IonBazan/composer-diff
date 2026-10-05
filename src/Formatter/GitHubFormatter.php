<?php

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;

class GitHubFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function render(DiffEntries $prodEntries, DiffEntries $devEntries, bool $withUrls, bool $withLicenses): void
    {
        $this->renderSingle($prodEntries, 'Prod Packages', $withUrls, $withLicenses);
        $this->renderSingle($devEntries, 'Dev Packages', $withUrls, $withLicenses);
    }

    /**
     * {@inheritdoc}
     */
    public function renderSingle(DiffEntries $entries, string $title, bool $withUrls, bool $withLicenses): void
    {
        if (!\count($entries)) {
            return;
        }

        $message = $this->escapeData(implode("\n", $this->transformEntries($entries, $withUrls, $withLicenses)));
        $this->output->writeln(sprintf('::notice title=%s::%s', str_replace([':', ','], ['%3A', '%2C'], $this->escapeData($title)), $message));
    }

    private function escapeData(string $data): string
    {
        return str_replace(['%', "\r", "\n"], ['%25', '%0D', '%0A'], $data);
    }

    /**
     * @return string[]
     */
    private function transformEntries(DiffEntries $entries, bool $withUrls, bool $withLicenses): array
    {
        $rows = [];

        foreach ($entries as $entry) {
            $rows[] = $this->transformEntry($entry, $withUrls, $withLicenses);
        }

        return $rows;
    }

    private function transformEntry(DiffEntry $entry, bool $withUrls, bool $withLicenses): string
    {
        $url = $withUrls ? $entry->getUrl() : null;
        $url = (null !== $url) ? ' '.$url : '';
        $licenses = $withLicenses ? implode(', ', $entry->getLicenses()) : '';
        $licenses = ('' !== $licenses) ? ' (License: '.$licenses.')' : '';

        if ($entry->isInstall()) {
            return sprintf(
                ' - Install %s (%s)%s%s',
                $entry->getDisplayName(),
                $entry->getTargetVersion(),
                $url,
                $licenses
            );
        }

        if ($entry->isRemove()) {
            return sprintf(
                ' - Uninstall %s (%s)%s%s',
                $entry->getDisplayName(),
                $entry->getBaseVersion(),
                $url,
                $licenses
            );
        }

        return sprintf(
            ' - %s %s (%s => %s)%s%s',
            ucfirst($entry->getType()),
            $entry->getDisplayName(),
            $entry->getBaseVersion(),
            $entry->getTargetVersion(),
            $url,
            $licenses
        );
    }
}
