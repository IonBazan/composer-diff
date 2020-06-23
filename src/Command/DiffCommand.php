<?php

namespace IonBazan\ComposerDiff\Command;

use Composer\Command\BaseCommand;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use IonBazan\ComposerDiff\MarkdownTable;
use IonBazan\ComposerDiff\PackageDiff;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DiffCommand extends BaseCommand
{
    /**
     * @var PackageDiff
     */
    protected $packageDiff;

    public function __construct(PackageDiff $packageDiff)
    {
        $this->packageDiff = $packageDiff;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('diff')
            ->setDescription('Displays package diff')
            ->addOption('base', 'b', InputOption::VALUE_REQUIRED, 'Base composer.lock file path or git ref', 'HEAD:composer.lock')
            ->addOption('target', 't', InputOption::VALUE_REQUIRED, 'Target composer.lock file path or git ref', 'composer.lock')
            ->addOption('no-dev', null, InputOption::VALUE_NONE, 'Ignore dev dependencies')
            ->addOption('no-prod', null, InputOption::VALUE_NONE, 'Ignore prod dependencies')
            ->addOption('with-platform', 'p', InputOption::VALUE_NONE, 'Include platform dependencies (PHP version, extensions, etc.)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $base = $input->getOption('base');
        $target = $input->getOption('target');
        $withPlatform = $input->getOption('with-platform');

        if (!$input->getOption('no-prod')) {
            $operations = $this->packageDiff->getPackageDiff($base, $target, false, $withPlatform);
            $this->displayTable($operations, 'Prod Packages', $output);
        }

        if (!$input->getOption('no-dev')) {
            $operations = $this->packageDiff->getPackageDiff($base, $target, true, $withPlatform);
            $this->displayTable($operations, 'Dev Packages', $output);
        }

        return 0;
    }

    protected function displayTable(array $operations, $header, OutputInterface $output)
    {
        if (!\count($operations)) {
            return;
        }

        $table = new MarkdownTable($output);
        $table->setHeaders(array($header, 'Base', 'Target'));

        foreach ($operations as $operation) {
            $table->addRow($this->getTableRow($operation));
        }

        $table->render();
        $output->writeln('');
    }

    protected function getTableRow(OperationInterface $operation)
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
