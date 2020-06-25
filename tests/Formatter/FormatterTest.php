<?php

namespace IonBazan\ComposerDiff\Tests\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
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
        $formatter->render(array(), 'Test');
        $this->assertEmpty($this->getDisplay($output));
    }

    public function testItRendersTheListOfOperations()
    {
        $output = new StreamOutput(fopen('php://memory', 'wb', false));
        $formatter = $this->getFormatter($output);
        $formatter->render(array(
            new InstallOperation($this->getPackage('a/package-1', '1.0.0')),
            new UpdateOperation($this->getPackage('a/package-2', '1.0.0'), $this->getPackage('a/package-2', '1.2.0')),
            new UpdateOperation($this->getPackage('a/package-3', '2.0.0'), $this->getPackage('a/package-3', '1.1.1')),
            new UninstallOperation($this->getPackage('a/package-4', '0.1.1')),
        ), 'Test');
        $this->assertSame($this->getSampleOutput(), $this->getDisplay($output));
    }

    public function testItFailsWithInvalidOperation()
    {
        $output = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')->getMock();
        $this->setExpectedException('InvalidArgumentException', 'Invalid operation');
        $this->getFormatter($output)->render(array(
            $this->getMockBuilder('Composer\DependencyResolver\Operation\OperationInterface')->getMock(),
        ), 'Test');
    }

    /**
     * @return Formatter
     */
    abstract protected function getFormatter(OutputInterface $output);

    /**
     * @return string
     */
    abstract protected function getSampleOutput();

    protected function getDisplay(OutputInterface $output)
    {
        rewind($output->getStream());

        return stream_get_contents($output->getStream());
    }
}
