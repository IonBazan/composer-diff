<?php

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\PackageInterface;
use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
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
    public function getUrl(OperationInterface $operation)
    {
        if ($operation instanceof UpdateOperation) {
            return $this->getCompareUrl($operation->getInitialPackage(), $operation->getTargetPackage());
        }

        if ($operation instanceof InstallOperation || $operation instanceof UninstallOperation) {
            return $this->getReleaseUrl($operation->getPackage());
        }

        return null;
    }

    /**
     * @return string|null
     */
    private function getCompareUrl(PackageInterface $basePackage, PackageInterface $targetPackage)
    {
        $generator = $this->generators->get($targetPackage);

        if (!$generator) {
            return null;
        }

        return $generator->getCompareUrl($basePackage, $targetPackage);
    }

    /**
     * @return string|null
     */
    private function getReleaseUrl(PackageInterface $package)
    {
        $generator = $this->generators->get($package);

        if (!$generator) {
            return null;
        }

        return $generator->getReleaseUrl($package);
    }

    /**
     * @param string $url
     * @param string $title
     *
     * @return string
     */
    abstract protected function formatUrl($url, $title);

    /**
     * @return bool
     */
    protected static function isUpgrade(UpdateOperation $operation)
    {
        $versionParser = new VersionParser();
        $normalizedFrom = $versionParser->normalize($operation->getInitialPackage()->getVersion());
        $normalizedTo = $versionParser->normalize($operation->getTargetPackage()->getVersion());

        $sorted = Semver::sort(array($normalizedTo, $normalizedFrom));

        return $sorted[0] === $normalizedFrom;
    }
}
