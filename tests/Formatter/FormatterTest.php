<?php

namespace IonBazan\ComposerDiff\Tests\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Formatter\Formatter;
use IonBazan\ComposerDiff\Tests\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

abstract class FormatterTest extends TestCase
{
    public function testItNoopsWhenListIsEmpty(): void
    {
        $stream = fopen('php://memory', 'wb', false);
        assert(false !== $stream);
        $output = new StreamOutput($stream);
        $formatter = $this->getFormatter($output);
        $formatter->render(new DiffEntries([]), new DiffEntries([]), true, false);
        $this->assertSame(static::getEmptyOutput(), $this->getDisplay($output));
    }

    /**
     * @testWith   [false, false]
     *             [false, true]
     *             [true, false]
     *             [true, true]
     *             [false, false, true]
     *             [false, true, true]
     *             [true, false, true]
     *             [true, true, true]
     */
    public function testItRendersTheListOfOperations(bool $withUrls, bool $withLicenses, bool $decorated = false): void
    {
        $stream = fopen('php://memory', 'wb', false);
        assert(false !== $stream);
        $output = new StreamOutput($stream, OutputInterface::VERBOSITY_NORMAL, $decorated);
        $this->getFormatter($output)->render(
            $this->getEntries($this->getSampleProdOperations(), $this->getGenerators()),
            $this->getEntries($this->getSampleDevOperations(), $this->getGenerators()),
            $withUrls,
            $withLicenses
        );
        $this->assertSame($this->getSampleOutput($withUrls, $withLicenses, $decorated), $this->getDisplay($output));
    }

    public function testItFailsWithInvalidOperation(): void
    {
        $output = $this->getMockBuilder(OutputInterface::class)->getMock();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid operation');
        $this->getFormatter($output)->render($this->getEntries([
            $this->getMockBuilder(OperationInterface::class)->getMock(),
        ], $this->getGenerators()), $this->getEntries([], $this->getGenerators()), false, false);
    }

    abstract protected function getFormatter(OutputInterface $output): Formatter;

    abstract protected function getSampleOutput(bool $withUrls, bool $withLicenses, bool $decorated): string;

    protected static function getEmptyOutput(): string
    {
        return '';
    }

    protected function getDisplay(StreamOutput $output): string
    {
        rewind($output->getStream());

        return stream_get_contents($output->getStream());
    }

    protected function supportsLinks(): bool
    {
        return true;
    }

    /**
     * @return OperationInterface[]
     */
    private function getSampleProdOperations(): array
    {
        return [
            new InstallOperation($this->getPackage('a/package-1', '1.0.0')),
            new InstallOperation($this->getPackage('a/no-link-1', '1.0.0')),
            new UpdateOperation($this->getPackage('a/package-2', '1.0.0'), $this->getPackage('a/package-2', '1.2.0')),
            new UpdateOperation($this->getPackage('a/package-3', '2.0.0'), $this->getPackage('a/package-3', '1.1.1')),
            new UpdateOperation($this->getPackage('a/no-link-2', '2.0.0'), $this->getPackage('a/no-link-2', '1.1.1')),
            new UpdateOperation($this->getPackage('php', '>=7.4.6'), $this->getPackage('php', '^8.0')),
        ];
    }

    /**
     * @return OperationInterface[]
     */
    private function getSampleDevOperations(): array
    {
        return [
            new UpdateOperation($this->getCompletePackage('a/package-5', 'dev-master', 'dev-master 1234567'), $this->getPackage('a/package-5', '1.1.1')),
            new UninstallOperation($this->getCompletePackage('a/package-4', '0.1.1', null, ['MIT', 'BSD-3-Clause'])),
            new UninstallOperation($this->getCompletePackage('a/no-link-2', '0.1.1', null, ['MIT'])),
        ];
    }
}
