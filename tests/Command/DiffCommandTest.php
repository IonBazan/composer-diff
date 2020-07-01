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
        $tester = new CommandTester(new DiffCommand($diff, array('gitlab2.org')));
        $diff->expects($this->once())
            ->method('getPackageDiff')
            ->with($this->isType('string'), $this->isType('string'), false, false)
            ->willReturn(array(
                new InstallOperation($this->getPackageWithSource('a/package-1', '1.0.0', 'github.com')),
                new UpdateOperation($this->getPackageWithSource('a/package-2', '1.0.0', 'github.com'), $this->getPackageWithSource('a/package-2', '1.2.0', 'github.com')),
                new UninstallOperation($this->getPackageWithSource('a/package-3', '0.1.1', 'github.com')),
                new UninstallOperation($this->getPackageWithSource('a/package-4', '0.1.1', 'gitlab.org')),
                new UninstallOperation($this->getPackageWithSource('a/package-5', '0.1.1', 'gitlab2.org')),
                new UninstallOperation($this->getPackageWithSource('a/package-6', '0.1.1', 'gitlab3.org')),
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
| a/package-4   | 0.1.1 | Removed |
| a/package-5   | 0.1.1 | Removed |
| a/package-6   | 0.1.1 | Removed |


OUTPUT
                ,
                array(
                    '--no-dev' => null,
                ),
            ),
            'Markdown with URLs' => array(
                <<<OUTPUT
| Prod Packages | Base  | Target  | Link                                       |
|---------------|-------|---------|--------------------------------------------|
| a/package-1   | New   | 1.0.0   | [Compare](github.com/releases/tag/1.0.0)   |
| a/package-2   | 1.0.0 | 1.2.0   | [Compare](github.com/compare/1.0.0..1.2.0) |
| a/package-3   | 0.1.1 | Removed | [Compare](github.com/releases/tag/0.1.1)   |
| a/package-4   | 0.1.1 | Removed | [Compare](gitlab.org/tags/0.1.1)           |
| a/package-5   | 0.1.1 | Removed | [Compare](gitlab2.org/tags/0.1.1)          |
| a/package-6   | 0.1.1 | Removed |                                            |


OUTPUT
            ,
                array(
                    '--no-dev' => null,
                    '-l' => null,
                ),
            ),
            'Markdown with URLs and custom gitlab domains' => array(
                <<<OUTPUT
| Prod Packages | Base  | Target  | Link                                       |
|---------------|-------|---------|--------------------------------------------|
| a/package-1   | New   | 1.0.0   | [Compare](github.com/releases/tag/1.0.0)   |
| a/package-2   | 1.0.0 | 1.2.0   | [Compare](github.com/compare/1.0.0..1.2.0) |
| a/package-3   | 0.1.1 | Removed | [Compare](github.com/releases/tag/0.1.1)   |
| a/package-4   | 0.1.1 | Removed | [Compare](gitlab.org/tags/0.1.1)           |
| a/package-5   | 0.1.1 | Removed | [Compare](gitlab2.org/tags/0.1.1)          |
| a/package-6   | 0.1.1 | Removed | [Compare](gitlab3.org/tags/0.1.1)          |


OUTPUT
            ,
                array(
                    '--no-dev' => null,
                    '-l' => null,
                    '--gitlab-domains' => array('gitlab3.org'),
                ),
            ),
            'Markdown list' => array(
                <<<OUTPUT
Prod Packages
=============

 - Install a/package-1 (1.0.0)
 - Upgrade a/package-2 (1.0.0 => 1.2.0)
 - Uninstall a/package-3 (0.1.1)
 - Uninstall a/package-4 (0.1.1)
 - Uninstall a/package-5 (0.1.1)
 - Uninstall a/package-6 (0.1.1)


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
