<?php

namespace IonBazan\ComposerDiff\Tests\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
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

abstract class FormatterTest extends TestCase
{
    public function testItNoopsWhenListIsEmpty()
    {
        $output = new StreamOutput(fopen('php://memory', 'wb', false));
        $formatter = $this->getFormatter($output, $this->getGenerators());
        $formatter->render(new DiffEntries(array()), new DiffEntries(array()), true);
        $this->assertSame(static::getEmptyOutput(), $this->getDisplay($output));
    }

    public function testGetUrlReturnsNullForInvalidOperation()
    {
        $output = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')->getMock();
        $operation = $this->getMockBuilder('Composer\DependencyResolver\Operation\OperationInterface')->getMock();
        $formatter = $this->getFormatter($output, $this->getGenerators());
        $this->assertNull($formatter->getUrl(new DiffEntry($operation)));
    }

    public function testGetProjectUrlReturnsNullForInvalidOperation()
    {
        $output = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')->getMock();
        $operation = $this->getMockBuilder('Composer\DependencyResolver\Operation\OperationInterface')->getMock();
        $formatter = $this->getFormatter($output, $this->getGenerators());
        $this->assertNull($formatter->getProjectUrl($operation));
    }

    /**
     * @param bool $withUrls
     * @param bool $decorated
     *
     * @testWith   [false]
     *             [true]
     *             [false, true]
     *             [true, true]
     */
    public function testItRendersTheListOfOperations($withUrls, $decorated = false)
    {
        $output = new StreamOutput(fopen('php://memory', 'wb', false), OutputInterface::VERBOSITY_NORMAL, $decorated);
        $this->getFormatter($output, $this->getGenerators())->render(
            $this->getEntries($this->getSampleProdOperations()),
            $this->getEntries($this->getSampleDevOperations()),
            $withUrls
        );
        $this->assertSame($this->getSampleOutput($withUrls, $decorated), $this->getDisplay($output));
    }

    public function testItFailsWithInvalidOperation()
    {
        $output = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')->getMock();
        $this->setExpectedException('InvalidArgumentException', 'Invalid operation');
        $this->getFormatter($output, $this->getGenerators())->render($this->getEntries(array(
            $this->getMockBuilder('Composer\DependencyResolver\Operation\OperationInterface')->getMock(),
        )), $this->getEntries(array()), false);
    }

    /**
     * @return Formatter
     */
    abstract protected function getFormatter(OutputInterface $output, GeneratorContainer $generators);

    /**
     * @param bool $withUrls
     * @param bool $decorated
     *
     * @return string
     */
    abstract protected function getSampleOutput($withUrls, $decorated);

    /**
     * @return string
     */
    protected static function getEmptyOutput()
    {
        return '';
    }

    /**
     * @return false|string
     */
    protected function getDisplay(OutputInterface $output)
    {
        rewind($output->getStream());

        return stream_get_contents($output->getStream());
    }

    /**
     * @return bool
     */
    protected function supportsLinks()
    {
        return method_exists('Symfony\Component\Console\Formatter\OutputFormatterStyle', 'setHref');
    }

    /**
     * @return MockObject|GeneratorContainer
     */
    protected function getGenerators()
    {
        $generator = $this->getMockBuilder('IonBazan\ComposerDiff\Url\UrlGenerator')->getMock();
        $generator->method('getCompareUrl')->willReturnCallback(function (PackageInterface $base, PackageInterface $target) {
            return sprintf('https://example.com/c/%s..%s', $base->getVersion(), $target->getVersion());
        });
        $generator->method('getReleaseUrl')->willReturnCallback(function (PackageInterface $package) {
            return sprintf('https://example.com/r/%s', $package->getVersion());
        });
        $generator->method('getProjectUrl')->willReturnCallback(function (PackageInterface $package) {
            return sprintf('https://example.com/r/%s', $package->getName());
        });

        $generators = $this->getMockBuilder('IonBazan\ComposerDiff\Url\GeneratorContainer')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
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

    /**
     * @return OperationInterface[]
     */
    private function getSampleProdOperations()
    {
        return array(
            new InstallOperation($this->getPackage('a/package-1', '1.0.0')),
            new InstallOperation($this->getPackage('a/no-link-1', '1.0.0')),
            new UpdateOperation($this->getPackage('a/package-2', '1.0.0'), $this->getPackage('a/package-2', '1.2.0')),
            new UpdateOperation($this->getPackage('a/package-3', '2.0.0'), $this->getPackage('a/package-3', '1.1.1')),
            new UpdateOperation($this->getPackage('a/no-link-2', '2.0.0'), $this->getPackage('a/no-link-2', '1.1.1')),
            new UpdateOperation($this->getPackage('php', '>=7.4.6'), $this->getPackage('php', '^8.0')),
        );
    }

    /**
     * @return OperationInterface[]
     */
    private function getSampleDevOperations()
    {
        return array(
            new UpdateOperation($this->getPackage('a/package-5', 'dev-master', 'dev-master 1234567'), $this->getPackage('a/package-5', '1.1.1')),
            new UninstallOperation($this->getPackage('a/package-4', '0.1.1')),
            new UninstallOperation($this->getPackage('a/no-link-2', '0.1.1')),
        );
    }
}
