<?php

namespace IonBazan\ComposerDiff\Tests\Command;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use IonBazan\ComposerDiff\Command\DiffCommand;
use IonBazan\ComposerDiff\Tests\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DiffCommandTest extends TestCase
{
    /**
     * @param string $expectedOutput
     *
     * @dataProvider outputDataProvider
     */
    public function testItGeneratesReportInGivenFormat($expectedOutput, array $options)
    {
        $diff = $this->getMockBuilder('IonBazan\ComposerDiff\PackageDiff')->getMock();
        $tester = new CommandTester(new DiffCommand($diff));
        $diff->expects($this->once())
            ->method('getPackageDiff')
            ->with($this->isType('string'), $this->isType('string'), false, false)
            ->willReturn(array(
                new InstallOperation($this->getPackage('a/package-1', '1.0.0')),
                new UpdateOperation($this->getPackage('a/package-2', '1.0.0'), $this->getPackage('a/package-2', '1.2.0')),
                new UninstallOperation($this->getPackage('a/package-3', '0.1.1')),
            ))
        ;
        $result = $tester->execute($options);
        $this->assertSame(0, $result);
        $this->assertSame($expectedOutput, $tester->getDisplay());
    }

    public function outputDataProvider()
    {
        return array(
            'Markdown table' => array(
                <<<OUTPUT
| Prod Packages | Base  | Target  |
|---------------|-------|---------|
| a/package-1   | New   | 1.0.0   |
| a/package-2   | 1.0.0 | 1.2.0   |
| a/package-3   | 0.1.1 | Removed |


OUTPUT
                ,
                array(
                    '--no-dev' => null,
                ),
            ),
            'Markdown list' => array(
                <<<OUTPUT
Prod Packages
=============

 - Install a/package-1 (1.0.0)
 - Upgrade a/package-2 (1.0.0 => 1.2.0)
 - Uninstall a/package-3 (0.1.1)


OUTPUT
            ,
                array(
                    '--no-dev' => null,
                    '-f' => 'mdlist',
                ),
            ),
        );
    }
}
