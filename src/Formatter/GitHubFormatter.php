<?php

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
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

        $message = str_replace("\n", '%0A', implode("\n", $this->transformEntries($entries, $withUrls, $withLicenses)));
        $this->output->writeln(sprintf('::notice title=%s::%s', $title, $message));
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
        $operation = $entry->getOperation();
        $url = $withUrls ? $this->getUrl($entry) : null;
        $url = (null !== $url) ? ' '.$url : '';
        $licenses = $withLicenses ? $this->getLicenses($entry) : null;
        $licenses = (null !== $licenses) ? ' (License: '.$licenses . ')' : '';

        if ($operation instanceof InstallOperation) {
            return sprintf(
                ' - Install %s (%s)%s%s',
                $operation->getPackage()->getName(),
                $operation->getPackage()->getFullPrettyVersion(),
                $url,
                $licenses
            );
        }

        if ($operation instanceof UpdateOperation) {
            return sprintf(
                ' - %s %s (%s => %s)%s%s',
                ucfirst($entry->getType()),
                $operation->getInitialPackage()->getName(),
                $operation->getInitialPackage()->getFullPrettyVersion(),
                $operation->getTargetPackage()->getFullPrettyVersion(),
                $url,
                $licenses
            );
        }

        if ($operation instanceof UninstallOperation) {
            return sprintf(
                ' - Uninstall %s (%s)%s%s',
                $operation->getPackage()->getName(),
                $operation->getPackage()->getFullPrettyVersion(),
                $url,
                $licenses
            );
        }

        throw new \InvalidArgumentException('Invalid operation');
    }
}
