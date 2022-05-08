<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
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

    private function terminalLink(?string $url, string $title): string
    {
        return null !== $url ? sprintf('<href=%s>%s</>', $url, $title) : $title;
    }

    public function getUrl(DiffEntry $entry): ?string
    {
        $operation = $entry->getOperation();

        if ($operation instanceof UpdateOperation) {
            return $this->generators->getCompareUrl($operation->getInitialPackage(), $operation->getTargetPackage());
        }

        if ($operation instanceof InstallOperation || $operation instanceof UninstallOperation) {
            return $this->generators->getReleaseUrl($operation->getPackage());
        }

        return null;
    }

    public function getProjectUrl(DiffEntry $entry): ?string
    {
        $package = $entry->getPackage();

        if (null === $package) {
            return null;
        }

        return $this->generators->getProjectUrl($package);
    }

    protected function getDecoratedPackageName(DiffEntry $entry): string
    {
        $package = $entry->getPackage();

        if (null === $package) {
            return '';
        }

        return $this->terminalLink($this->getProjectUrl($entry), $package->getName());
    }
}
