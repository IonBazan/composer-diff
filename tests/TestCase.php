<?php declare(strict_types=1);

namespace IonBazan\ComposerDiff\Tests;

use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\Package\PackageInterface;
use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackage(string $name, string $version, ?string $fullVersion = null): PackageInterface
    {
        $package = $this->createMock(PackageInterface::class);
        $package->method('getName')->willReturn($name);
        $package->method('getVersion')->willReturn($version);
        $package->method('getPrettyVersion')->willReturn($version);
        $package->method('getFullPrettyVersion')->willReturn($fullVersion ?? $version);

        return $package;
    }

    protected function getPackageWithSource(string $name, string $version, string $sourceUrl, ?string $sourceReference = null): PackageInterface
    {
        $package = $this->getPackage($name, $version, $sourceReference);
        $package->method('getSourceUrl')->willReturn($sourceUrl);
        $package->method('getSourceReference')->willReturn($sourceReference);
        $package->method('isDev')->willReturn(0 === strpos($version, 'dev-') || '-dev' === substr($version, -4));

        return $package;
    }

    /**
     * @param OperationInterface[] $operations
     */
    protected function getEntries(array $operations): DiffEntries
    {
        return new DiffEntries(array_map(function (OperationInterface $operation) {
            return new DiffEntry($operation);
        }, $operations));
    }
}
