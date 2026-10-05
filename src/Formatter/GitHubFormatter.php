<?php

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;

class GitHubFormatter extends AbstractFormatter
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

        $message = $this->escapeData(implode("\n", $this->transformEntries($entries, $withUrls, $withLicenses)));
        $this->output->writeln(sprintf('::notice title=%s::%s', str_replace(array(':', ','), array('%3A', '%2C'), $this->escapeData($title)), $message));
    }

    /**
     * @param string $data
     *
     * @return string
     */
    private function escapeData($data)
    {
        return str_replace(array('%', "\r", "\n"), array('%25', '%0D', '%0A'), $data);
    }

    /**
     * @param bool $withUrls
     * @param bool $withLicenses
     *
     * @return string[]
     */
    private function transformEntries(DiffEntries $entries, $withUrls, $withLicenses)
    {
        $rows = array();

        foreach ($entries as $entry) {
            $rows[] = $this->transformEntry($entry, $withUrls, $withLicenses);
        }

        return $rows;
    }

    /**
     * @param bool $withUrls
     * @param bool $withLicenses
     *
     * @return string
     */
    private function transformEntry(DiffEntry $entry, $withUrls, $withLicenses)
    {
        $url = $withUrls ? $entry->getUrl() : null;
        $url = (null !== $url) ? ' '.$url : '';
        $licenses = $withLicenses ? implode(', ', $entry->getLicenses()) : '';
        $licenses = ('' !== $licenses) ? ' (License: '.$licenses.')' : '';

        if ($entry->isInstall()) {
            return sprintf(
                ' - Install %s (%s)%s%s',
                $entry->getPackageName(),
                $entry->getTargetVersion(),
                $url,
                $licenses
            );
        }

        if ($entry->isRemove()) {
            return sprintf(
                ' - Uninstall %s (%s)%s%s',
                $entry->getPackageName(),
                $entry->getBaseVersion(),
                $url,
                $licenses
            );
        }

        return sprintf(
            ' - %s %s (%s => %s)%s%s',
            ucfirst($entry->getType()),
            $entry->getPackageName(),
            $entry->getBaseVersion(),
            $entry->getTargetVersion(),
            $url,
            $licenses
        );
    }
}
