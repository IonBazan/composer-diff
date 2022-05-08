<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Tests\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\PackageInterface;
use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Formatter\Formatter;
use IonBazan\ComposerDiff\Tests\TestCase;
use IonBazan\ComposerDiff\Url\GeneratorContainer;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use IonBazan\ComposerDiff\Url\UrlGenerator;
use Composer\DependencyResolver\Operation\OperationInterface;

abstract class FormatterTest extends TestCase
{
    public function testItNoopsWhenListIsEmpty(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'wb', false));
        $formatter = $this->getFormatter($output, $this->getGenerators());
        $formatter->render(new DiffEntries([]), new DiffEntries([]), true);
        $this->assertSame(static::getEmptyOutput(), $this->getDisplay($output));
    }

    public function testGetUrlReturnsNullForInvalidOperation(): void
    {
        $output = $this->createMock(OutputInterface::class);
        $operation = $this->createMock(OperationInterface::class);
        $formatter = $this->getFormatter($output, $this->getGenerators());
        $this->assertNull($formatter->getUrl(new DiffEntry($operation)));
    }

    public function testGetProjectUrlReturnsNullForInvalidOperation(): void
    {
        $output = $this->createMock(OutputInterface::class);
        $operation = $this->createMock(OperationInterface::class);
        $formatter = $this->getFormatter($output, $this->getGenerators());
        $this->assertNull($formatter->getProjectUrl(new DiffEntry($operation)));
    }

    /**
     * @testWith   [false]
     *             [true]
     */
    public function testItRendersTheListOfOperations(bool $withUrls): void
    {
        $output = new StreamOutput(fopen('php://memory', 'wb', false));
        $formatter = $this->getFormatter($output, $this->getGenerators());
        $prodPackages = [
            new InstallOperation($this->getPackage('a/package-1', '1.0.0')),
            new InstallOperation($this->getPackage('a/no-link-1', '1.0.0')),
            new UpdateOperation($this->getPackage('a/package-2', '1.0.0'), $this->getPackage('a/package-2', '1.2.0')),
            new UpdateOperation($this->getPackage('a/package-3', '2.0.0'), $this->getPackage('a/package-3', '1.1.1')),
            new UpdateOperation($this->getPackage('a/no-link-2', '2.0.0'), $this->getPackage('a/no-link-2', '1.1.1')),
            new UpdateOperation($this->getPackage('php', '>=7.4.6'), $this->getPackage('php', '^8.0')),
        ];
        $devPackages = [
            new UpdateOperation($this->getPackage('a/package-5', 'dev-master', 'dev-master 1234567'), $this->getPackage('a/package-5', '1.1.1')),
            new UninstallOperation($this->getPackage('a/package-4', '0.1.1')),
            new UninstallOperation($this->getPackage('a/no-link-2', '0.1.1')),
        ];
        $formatter->render($this->getEntries($prodPackages), $this->getEntries($devPackages), $withUrls);
        $this->assertSame($this->getSampleOutput($withUrls), $this->getDisplay($output));
    }

    public function testItFailsWithInvalidOperation(): void
    {
        $output = $this->createMock(OutputInterface::class);
        $this->expectExceptionObject(new \InvalidArgumentException('Invalid operation'));
        $this->getFormatter($output, $this->getGenerators())->render($this->getEntries([
            $this->createMock(OperationInterface::class),
        ]), $this->getEntries([]), false);
    }

    abstract protected function getFormatter(OutputInterface $output, GeneratorContainer $generators): Formatter;

    abstract protected function getSampleOutput(bool $withUrls): string;

    protected static function getEmptyOutput(): string
    {
        return '';
    }

    protected function getDisplay(OutputInterface $output): string
    {
        rewind($output->getStream());

        return (string) stream_get_contents($output->getStream());
    }

    /**
     * @return MockObject&GeneratorContainer
     */
    protected function getGenerators(): GeneratorContainer
    {
        $generator = $this->createMock(UrlGenerator::class);
        $generator->method('getCompareUrl')->willReturnCallback(function (PackageInterface $base, PackageInterface $target) {
            return sprintf('https://example.com/c/%s..%s', $base->getVersion(), $target->getVersion());
        });
        $generator->method('getReleaseUrl')->willReturnCallback(function (PackageInterface $package) {
            return sprintf('https://example.com/r/%s', $package->getVersion());
        });
        $generator->method('getProjectUrl')->willReturnCallback(function (PackageInterface $package) {
            return sprintf('https://example.com/r/%s', $package->getName());
        });

        $generators = $this->createPartialMock(GeneratorContainer::class, ['get']);
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
