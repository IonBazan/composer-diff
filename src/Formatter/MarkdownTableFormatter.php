<?php

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use IonBazan\ComposerDiff\Formatter\Helper\Table;
use IonBazan\ComposerDiff\PackageDiff;

class MarkdownTableFormatter extends MarkdownFormatter
{
    /**
     * {@inheritdoc}
     */
    public function render(array $prodOperations, array $devOperations, $withUrls)
    {
        $this->renderSingle($prodOperations, 'Prod Packages', $withUrls);
        $this->renderSingle($devOperations, 'Dev Packages', $withUrls);
    }

    /**
     * {@inheritdoc}
     */
    public function renderSingle(array $operations, $title, $withUrls)
    {
        if (!\count($operations)) {
            return;
        }

        $rows = array();

        foreach ($operations as $operation) {
            $row = $this->getTableRow($operation);

            if ($withUrls) {
                $row[] = $this->formatUrl($this->getUrl($operation), 'Compare');
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
    private function getTableRow(OperationInterface $operation)
    {
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
                PackageDiff::isUpgrade($operation) ? '<fg=cyan>Upgraded</>' : '<fg=yellow>Downgraded</>',
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
