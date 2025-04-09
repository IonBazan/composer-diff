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
    public function testItNoopsWhenListIsEmpty()
    {
        $output = new StreamOutput(fopen('php://memory', 'wb', false));
        $formatter = $this->getFormatter($output);
        $formatter->render(new DiffEntries(array()), new DiffEntries(array()), true, false);
        $this->assertSame(static::getEmptyOutput(), $this->getDisplay($output));
    }

    /**
     * @param bool $withUrls
     * @param bool $decorated
     *
     * @testWith   [false, false]
     *             [false, true]
     *             [true, false]
     *             [true, true]
     *             [false, false, true]
     *             [false, true, true]
     *             [true, false, true]
     *             [true, true, true]
     */
    public function testItRendersTheListOfOperations($withUrls, $withLicenses, $decorated = false)
    {
        $output = new StreamOutput(fopen('php://memory', 'wb', false), OutputInterface::VERBOSITY_NORMAL, $decorated);
        $this->getFormatter($output)->render(
            $this->getEntries($this->getSampleProdOperations(), $this->getGenerators()),
            $this->getEntries($this->getSampleDevOperations(), $this->getGenerators()),
            $withUrls,
            $withLicenses
        );
        $this->assertSame($this->getSampleOutput($withUrls, $withLicenses, $decorated), $this->getDisplay($output));
    }

    public function testItFailsWithInvalidOperation()
    {
        $output = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')->getMock();
        $this->setExpectedException('InvalidArgumentException', 'Invalid operation');
        $this->getFormatter($output)->render($this->getEntries(array(
            $this->getMockBuilder('Composer\DependencyResolver\Operation\OperationInterface')->getMock(),
        ), $this->getGenerators()), $this->getEntries(array(), $this->getGenerators()), false, false);
    }

    /**
     * @return Formatter
     */
    abstract protected function getFormatter(OutputInterface $output);

    /**
     * @param bool $withUrls
     * @param bool $withLicenses
     * @param bool $decorated
     *
     * @return string
     */
    abstract protected function getSampleOutput($withUrls, $withLicenses, $decorated);

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
            new UpdateOperation($this->getCompletePackage('a/package-5', 'dev-master', 'dev-master 1234567'), $this->getPackage('a/package-5', '1.1.1')),
            new UninstallOperation($this->getCompletePackage('a/package-4', '0.1.1', null, array('MIT', 'BSD-3-Clause'))),
            new UninstallOperation($this->getCompletePackage('a/no-link-2', '0.1.1', null, array('MIT'))),
        );
    }
}
