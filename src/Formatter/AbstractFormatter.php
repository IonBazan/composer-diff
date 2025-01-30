<?php

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\CompletePackageInterface;
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

    /**
     * @return string|null
     */
    public function getUrl(DiffEntry $entry)
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

    /**
     * @return string|null
     */
    public function getLicenses(DiffEntry $entry)
    {
        if (!$entry->getPackage() instanceof CompletePackageInterface) {
            return null;
        }

        $licenses = $entry->getPackage()->getLicense();

        if (empty($licenses)) {
            return null;
        }

        return implode(', ', $licenses);
    }

    /**
     * @return string|null
     */
    public function getProjectUrl(OperationInterface $operation)
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

        return $this->generators->getProjectUrl($package);
    }

    /**
     * @return string
     */
    protected function getDecoratedPackageName(DiffEntry $entry)
    {
        $package = $entry->getPackage();

        if (null === $package) {
            return '';
        }

        return $this->terminalLink($this->getProjectUrl($entry->getOperation()), $package->getName());
    }

    /**
     * @param string|null $url
     * @param string      $title
     *
     * @return string
     */
    private function terminalLink($url, $title)
    {
        if (null === $url) {
            return $title;
        }

        // @phpstan-ignore function.alreadyNarrowedType
        return method_exists('Symfony\Component\Console\Formatter\OutputFormatterStyle', 'setHref') ? sprintf('<href=%s>%s</>', $url, $title) : $title;
    }
}
