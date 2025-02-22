<?php

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
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
                $row[] = $this->formatUrl($this->getUrl($entry), 'Compare');
            }

            if ($withLicenses) {
                $row[] = $this->getLicenses($entry);
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
        $operation = $entry->getOperation();
        $packageName = $this->getDecoratedPackageName($entry);
        $packageUrl = $withUrls ? $this->formatUrl($this->getProjectUrl($operation), $packageName) : $packageName;

        if ($operation instanceof InstallOperation) {
            return array(
                $packageUrl ?: $packageName,
                '<fg=green>New</>',
                '-',
                $operation->getPackage()->getFullPrettyVersion(),
            );
        }

        if ($operation instanceof UpdateOperation) {
            return array(
                $packageUrl ?: $packageName,
                $entry->isChange() ? '<fg=magenta>Changed</>' : ($entry->isUpgrade() ? '<fg=cyan>Upgraded</>' : '<fg=yellow>Downgraded</>'),
                $operation->getInitialPackage()->getFullPrettyVersion(),
                $operation->getTargetPackage()->getFullPrettyVersion(),
            );
        }

        if ($operation instanceof UninstallOperation) {
            return array(
                $packageUrl ?: $packageName,
                '<fg=red>Removed</>',
                $operation->getPackage()->getFullPrettyVersion(),
                '-',
            );
        }

        throw new \InvalidArgumentException('Invalid operation');
    }
}
