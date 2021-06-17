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
    public function render(DiffEntries $prodEntries, DiffEntries $devEntries, $withUrls)
    {
        $this->renderSingle($prodEntries, 'Prod Packages', $withUrls);
        $this->renderSingle($devEntries, 'Dev Packages', $withUrls);
    }

    /**
     * {@inheritdoc}
     */
    public function renderSingle(DiffEntries $entries, $title, $withUrls)
    {
        if (!\count($entries)) {
            return;
        }

        $rows = array();

        foreach ($entries as $entry) {
            $row = $this->getTableRow($entry);

            if ($withUrls) {
                $row[] = $this->formatUrl($this->getUrl($entry), 'Compare');
            }

            $rows[] = $row;
        }

        $table = new Table($this->output);
        $headers = array($title, 'Operation', 'Base', 'Target');

        if ($withUrls) {
            $headers[] = 'Link';
        }

        $table->setHeaders($headers)->setRows($rows)->render();
        $this->output->writeln('');
    }

    /**
     * @return string[]
     */
    private function getTableRow(DiffEntry $entry)
    {
        $operation = $entry->getOperation();
        if ($operation instanceof InstallOperation) {
            return array(
                $operation->getPackage()->getName(),
                '<fg=green>New</>',
                '-',
                $operation->getPackage()->getFullPrettyVersion(),
            );
        }

        if ($operation instanceof UpdateOperation) {
            return array(
                $operation->getInitialPackage()->getName(),
                $entry->isChange() ? '<fg=magenta>Changed</>' : ($entry->isUpgrade() ? '<fg=cyan>Upgraded</>' : '<fg=yellow>Downgraded</>'),
                $operation->getInitialPackage()->getFullPrettyVersion(),
                $operation->getTargetPackage()->getFullPrettyVersion(),
            );
        }

        if ($operation instanceof UninstallOperation) {
            return array(
                $operation->getPackage()->getName(),
                '<fg=red>Removed</>',
                $operation->getPackage()->getFullPrettyVersion(),
                '-',
            );
        }

        throw new \InvalidArgumentException('Invalid operation');
    }
}
