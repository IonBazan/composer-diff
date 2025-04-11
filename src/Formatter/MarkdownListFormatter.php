<?php

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
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
        $url = $withUrls ? $this->formatUrl($entry->getUrl($this->generators), 'Compare') : null;
        $url = (null !== $url && '' !== $url) ? ' '.$url : '';
        $licenses = $withLicenses ? implode(', ', $entry->getLicenses()) : '';
        $licenses = ('' !== $licenses) ? ' (License: '.$licenses.')' : '';
        $operation = $entry->getOperation();
        $package = $entry->getPackage();

        $packageName = $package ? $package->getName() : null;
        $packageUrl = $withUrls ? $this->formatUrl($entry->getProjectUrl($this->generators), $packageName) : $packageName;

        if ($operation instanceof InstallOperation) {
            return sprintf(
                ' - Install <fg=green>%s</> (<fg=yellow>%s</>)%s%s',
                $packageUrl ?: $packageName,
                $operation->getPackage()->getFullPrettyVersion(),
                $url,
                $licenses
            );
        }

        if ($operation instanceof UpdateOperation) {
            return sprintf(
                ' - %s <fg=green>%s</> (<fg=yellow>%s</> => <fg=yellow>%s</>)%s%s',
                ucfirst($entry->getType()),
                $packageUrl ?: $packageName,
                $operation->getInitialPackage()->getFullPrettyVersion(),
                $operation->getTargetPackage()->getFullPrettyVersion(),
                $url,
                $licenses
            );
        }

        if ($operation instanceof UninstallOperation) {
            return sprintf(
                ' - Uninstall <fg=green>%s</> (<fg=yellow>%s</>)%s%s',
                $packageUrl ?: $packageName,
                $operation->getPackage()->getFullPrettyVersion(),
                $url,
                $licenses
            );
        }

        throw new \InvalidArgumentException('Invalid operation');
    }
}
