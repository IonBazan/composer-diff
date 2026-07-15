<?php

namespace IonBazan\ComposerDiff\Tests;

use IonBazan\ComposerDiff\Url\UrlGenerator;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\Package\CompletePackageInterface;
use Composer\Package\PackageInterface;
use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Tests\Util\ComposerApplication;
use IonBazan\ComposerDiff\Url\GeneratorContainer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @return MockObject&PackageInterface
     */
    protected function getPackage(string $name, string $version, ?string $fullVersion = null): PackageInterface
    {
        $package = $this->getMockBuilder(PackageInterface::class)->getMock();
        $package->method('getName')->willReturn($name);
        $package->method('getVersion')->willReturn($version);
        $package->method('getPrettyVersion')->willReturn($version);
        $package->method('getFullPrettyVersion')->willReturn($fullVersion ?? $version);

        return $package;
    }

    /**
     * @return MockObject&PackageInterface
     */
    protected function getPackageWithSource(string $name, string $version, ?string $sourceUrl, ?string $sourceReference = null): PackageInterface
    {
        $package = $this->getPackage($name, $version, $sourceReference);
        $package->method('getSourceUrl')->willReturn($sourceUrl);
        $package->method('getSourceReference')->willReturn($sourceReference);
        $package->method('isDev')->willReturn(0 === strpos($version, 'dev-') || '-dev' === substr($version, -4));

        return $package;
    }

    /**
     * @param string[] $license
     *
     * @return MockObject&CompletePackageInterface
     */
    protected function getCompletePackage(string $name, string $version, ?string $fullVersion = null, array $license = []): CompletePackageInterface
    {
        $package = $this->getMockBuilder(CompletePackageInterface::class)->getMock();
        $package->method('getName')->willReturn($name);
        $package->method('getVersion')->willReturn($version);
        $package->method('getPrettyVersion')->willReturn($version);
        $package->method('getFullPrettyVersion')->willReturn($fullVersion ?? $version);
        $package->method('getLicense')->willReturn($license);

        return $package;
    }

    /**
     * @param OperationInterface[] $operations
     */
    protected function getEntries(array $operations, GeneratorContainer $urlGenerators): DiffEntries
    {
        return new DiffEntries(array_map(function (OperationInterface $operation) use ($urlGenerators): DiffEntry {
            return new DiffEntry($operation, $urlGenerators);
        }, $operations));
    }

    protected function getComposerApplication(): ComposerApplication
    {
        return new ComposerApplication();
    }

    /**
     * @return MockObject&GeneratorContainer
     */
    protected function getGenerators(): GeneratorContainer
    {
        $generator = $this->getMockBuilder(UrlGenerator::class)->getMock();
        $generator->method('getCompareUrl')->willReturnCallback(function (PackageInterface $base, PackageInterface $target): string {
            return sprintf('https://example.com/c/%s..%s', $base->getVersion(), $target->getVersion());
        });
        $generator->method('getReleaseUrl')->willReturnCallback(function (PackageInterface $package): string {
            return sprintf('https://example.com/r/%s', $package->getVersion());
        });
        $generator->method('getProjectUrl')->willReturnCallback(function (PackageInterface $package): string {
            return sprintf('https://example.com/r/%s', $package->getName());
        });

        $generators = $this->getMockBuilder(GeneratorContainer::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $generators->method('get')
            ->willReturnCallback(function (PackageInterface $package) use ($generator) {
                if ('php' === $package->getName() || false !== strpos($package->getName(), 'a/no-link')) {
                    return null;
                }

                return $generator;
            });

        return $generators;
    }
}
