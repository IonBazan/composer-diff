<?php

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use IonBazan\ComposerDiff\Formatter\Helper\MarkdownTable;
use Symfony\Component\Console\Output\OutputInterface;

class MarkdownTableFormatter extends AbstractFormatter
{
    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $operations, $title)
    {
        if (!\count($operations)) {
            return;
        }

        $rows = array();

        foreach ($operations as $operation) {
            $rows[] = $this->getTableRow($operation);
        }

        $table = new MarkdownTable($this->output);
        $table->setHeaders(array($title, 'Base', 'Target'))->setRows($rows)->render();
        $this->output->writeln('');
    }

    private function getTableRow(OperationInterface $operation)
    {
        if ($operation instanceof InstallOperation) {
            return array(
                $operation->getPackage()->getName(),
                'New',
                $operation->getPackage()->getFullPrettyVersion(),
            );
        }

        if ($operation instanceof UpdateOperation) {
            return array(
                $operation->getInitialPackage()->getName(),
                $operation->getInitialPackage()->getFullPrettyVersion(),
                $operation->getTargetPackage()->getFullPrettyVersion(),
            );
        }

        if ($operation instanceof UninstallOperation) {
            return array(
                $operation->getPackage()->getName(),
                $operation->getPackage()->getFullPrettyVersion(),
                'Removed',
            );
        }

        throw new \InvalidArgumentException('Invalid operation');
    }
}
