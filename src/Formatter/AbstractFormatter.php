<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\PackageInterface;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Url\GeneratorContainer;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractFormatter implements Formatter
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var GeneratorContainer
     */
    protected $generators;

    public function __construct(OutputInterface $output, GeneratorContainer $generators)
    {
        $this->output = $output;
        $this->generators = $generators;
    }

    public function getUrl(DiffEntry $entry): ?string
    {
        $operation = $entry->getOperation();

        if ($operation instanceof UpdateOperation) {
            return $this->getCompareUrl($operation->getInitialPackage(), $operation->getTargetPackage());
        }

        if ($operation instanceof InstallOperation || $operation instanceof UninstallOperation) {
            return $this->getReleaseUrl($operation->getPackage());
        }

        return null;
    }

    public function getProjectUrl(OperationInterface $operation): ?string
    {
        if ($operation instanceof UpdateOperation) {
            $package = $operation->getInitialPackage();
        }

        if ($operation instanceof InstallOperation || $operation instanceof UninstallOperation) {
            $package = $operation->getPackage();
        }

        if (!isset($package)) {
            return null;
        }

        $generator = $this->generators->get($package);

        if (!$generator) {
            return null;
        }

        return $generator->getProjectUrl($package);
    }

    private function getCompareUrl(PackageInterface $basePackage, PackageInterface $targetPackage): ?string
    {
        $generator = $this->generators->get($targetPackage);

        if (!$generator) {
            return null;
        }

        return $generator->getCompareUrl($basePackage, $targetPackage);
    }

    private function getReleaseUrl(PackageInterface $package): ?string
    {
        $generator = $this->generators->get($package);

        if (!$generator) {
            return null;
        }

        return $generator->getReleaseUrl($package);
    }
}
