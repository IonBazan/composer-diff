<?php

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Symfony\Component\Console\Output\OutputInterface;

class MarkdownListFormatter implements Formatter
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

        $this->output->writeln($title);
        $this->output->writeln(str_repeat('=', strlen($title)));
        $this->output->writeln('');

        foreach ($operations as $operation) {
            $this->output->writeln($this->getRow($operation));
        }

        $this->output->writeln('');
    }

    /**
     * @return string
     */
    protected function getRow(OperationInterface $operation)
    {
        if ($operation instanceof InstallOperation) {
            return sprintf(
                ' - Install %s (%s)',
                $operation->getPackage()->getName(),
                $operation->getPackage()->getFullPrettyVersion()
            );
        }

        if ($operation instanceof UpdateOperation) {
            return sprintf(
                ' - Update %s (%s => %s)',
                $operation->getInitialPackage()->getName(),
                $operation->getInitialPackage()->getFullPrettyVersion(),
                $operation->getTargetPackage()->getFullPrettyVersion()
            );
        }

        if ($operation instanceof UninstallOperation) {
            return sprintf(
                ' - Remove %s (%s)',
                $operation->getPackage()->getName(),
                $operation->getPackage()->getFullPrettyVersion()
            );
        }

        throw new \InvalidArgumentException('Invalid operation');
    }
}
