<?php

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;

class MarkdownListFormatter extends MarkdownFormatter
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

        $this->output->writeln($title);
        $this->output->writeln(str_repeat('=', strlen($title)));
        $this->output->writeln('');

        foreach ($entries as $entry) {
            $this->output->writeln($this->getRow($entry, $withUrls, $withLicenses));
        }

        $this->output->writeln('');
    }

    /**
     * @param bool $withUrls
     * @param bool $withLicenses
     *
     * @return string
     */
    private function getRow(DiffEntry $entry, $withUrls, $withLicenses)
    {
        $url = $withUrls ? $this->formatUrl($entry->getUrl(), 'Compare') : null;
        $url = (null !== $url && '' !== $url) ? ' '.$url : '';
        $licenses = $withLicenses ? implode(', ', $entry->getLicenses()) : '';
        $licenses = ('' !== $licenses) ? ' (License: '.$licenses.')' : '';

        $packageName = $entry->getPackageName();
        $packageUrl = $withUrls ? $this->formatUrl($entry->getProjectUrl(), $packageName) : $packageName;

        if ($entry->isInstall()) {
            return sprintf(
                ' - Install <fg=green>%s</> (<fg=yellow>%s</>)%s%s',
                $packageUrl ?: $packageName,
                $entry->getTargetVersion(),
                $url,
                $licenses
            );
        }

        if ($entry->isRemove()) {
            return sprintf(
                ' - Uninstall <fg=green>%s</> (<fg=yellow>%s</>)%s%s',
                $packageUrl ?: $packageName,
                $entry->getBaseVersion(),
                $url,
                $licenses
            );
        }

        return sprintf(
            ' - %s <fg=green>%s</> (<fg=yellow>%s</> => <fg=yellow>%s</>)%s%s',
            ucfirst($entry->getType()),
            $packageUrl ?: $packageName,
            $entry->getBaseVersion(),
            $entry->getTargetVersion(),
            $url,
            $licenses
        );
    }
}
