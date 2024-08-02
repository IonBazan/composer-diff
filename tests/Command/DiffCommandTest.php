<?php

namespace IonBazan\ComposerDiff\Tests\Command;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
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
        $application = $this->getComposerApplication();
        $command = new DiffCommand($diff, array('gitlab2.org'));
        $command->setApplication($application);
        $tester = new CommandTester($command);
        $diff->expects($this->once())
            ->method('getPackageDiff')
            ->with($this->isType('string'), $this->isType('string'), false, false)
            ->willReturn($this->getEntries(array(
                new InstallOperation($this->getPackageWithSource('a/package-1', '1.0.0', 'github.com')),
                new UpdateOperation($this->getPackageWithSource('a/package-2', '1.0.0', 'github.com'), $this->getPackageWithSource('a/package-2', '1.2.0', 'github.com')),
                new UninstallOperation($this->getPackageWithSource('a/package-3', '0.1.1', 'github.com')),
                new UninstallOperation($this->getPackageWithSource('a/package-4', '0.1.1', 'gitlab.org')),
                new UninstallOperation($this->getPackageWithSource('a/package-5', '0.1.1', 'gitlab2.org')),
                new UninstallOperation($this->getPackageWithSource('a/package-6', '0.1.1', 'gitlab3.org')),
                new UpdateOperation($this->getPackageWithSource('a/package-7', '1.2.0', 'github.com'), $this->getPackageWithSource('a/package-7', '1.0.0', 'github.com')),
            )))
        ;
        $result = $tester->execute($options);
        $this->assertSame(0, $result);
        $this->assertSame($expectedOutput, $tester->getDisplay());
    }

    /**
     * @param int                  $exitCode
     * @param OperationInterface[] $prodOperations
     * @param OperationInterface[] $devOperations
     *
     * @dataProvider strictDataProvider
     */
    public function testStrictMode($exitCode, array $prodOperations, array $devOperations)
    {
        $diff = $this->getMockBuilder('IonBazan\ComposerDiff\PackageDiff')->getMock();
        $application = $this->getComposerApplication();
        $command = new DiffCommand($diff, array('gitlab2.org'));
        $command->setApplication($application);
        $tester = new CommandTester($command);
        $diff->expects($this->exactly(2))
            ->method('getPackageDiff')
            ->with($this->isType('string'), $this->isType('string'), $this->isType('boolean'), false)
            ->willReturnOnConsecutiveCalls($this->getEntries($prodOperations), $this->getEntries($devOperations))
        ;
        $this->assertSame($exitCode, $tester->execute(array('--strict' => null)));
    }

    public function strictDataProvider()
    {
        return array(
            'No changes' => array(0, array(), array()),
            'Changes in prod and dev' => array(
                6,
                array(
                    new InstallOperation($this->getPackageWithSource('a/package-1', '1.0.0', 'github.com')),
                    new UpdateOperation($this->getPackageWithSource('a/package-2', '1.0.0', 'github.com'), $this->getPackageWithSource('a/package-2', '1.2.0', 'github.com')),
                ),
                array(
                    new UpdateOperation($this->getPackageWithSource('a/package-3', '1.0.0', 'github.com'), $this->getPackageWithSource('a/package-3', '1.2.0', 'github.com')),
                    new InstallOperation($this->getPackageWithSource('a/package-4', '1.0.0', 'github.com')),
                ),
            ),
            'Downgrades in prod and changes in dev' => array(
                14,
                array(
                    new InstallOperation($this->getPackageWithSource('a/package-1', '1.0.0', 'github.com')),
                    new UpdateOperation($this->getPackageWithSource('a/package-2', '1.2.0', 'github.com'), $this->getPackageWithSource('a/package-2', '1.0.0', 'github.com')),
                ),
                array(
                    new UpdateOperation($this->getPackageWithSource('a/package-3', '1.0.0', 'github.com'), $this->getPackageWithSource('a/package-3', '1.2.0', 'github.com')),
                    new InstallOperation($this->getPackageWithSource('a/package-4', '1.0.0', 'github.com')),
                ),
            ),
            'Changes in prod and downgrades in dev' => array(
                22,
                array(
                    new InstallOperation($this->getPackageWithSource('a/package-1', '1.0.0', 'github.com')),
                    new UpdateOperation($this->getPackageWithSource('a/package-2', '1.0.0', 'github.com'), $this->getPackageWithSource('a/package-2', '1.2.0', 'github.com')),
                ),
                array(
                    new UpdateOperation($this->getPackageWithSource('a/package-3', '1.2.0', 'github.com'), $this->getPackageWithSource('a/package-3', '1.0.0', 'github.com')),
                    new InstallOperation($this->getPackageWithSource('a/package-4', '1.0.0', 'github.com')),
                ),
            ),
            'Downgrades in both' => array(
                30,
                array(
                    new InstallOperation($this->getPackageWithSource('a/package-1', '1.0.0', 'github.com')),
                    new UpdateOperation($this->getPackageWithSource('a/package-2', '1.2.0', 'github.com'), $this->getPackageWithSource('a/package-2', '1.0.0', 'github.com')),
                ),
                array(
                    new UpdateOperation($this->getPackageWithSource('a/package-3', '1.2.0', 'github.com'), $this->getPackageWithSource('a/package-3', '1.0.0', 'github.com')),
                    new InstallOperation($this->getPackageWithSource('a/package-4', '1.0.0', 'github.com')),
                ),
            ),
        );
    }

    public function outputDataProvider()
    {
        return array(
            'Markdown table' => array(
                <<<OUTPUT
| Prod Packages | Operation  | Base  | Target |
|---------------|------------|-------|--------|
| a/package-1   | New        | -     | 1.0.0  |
| a/package-2   | Upgraded   | 1.0.0 | 1.2.0  |
| a/package-3   | Removed    | 0.1.1 | -      |
| a/package-4   | Removed    | 0.1.1 | -      |
| a/package-5   | Removed    | 0.1.1 | -      |
| a/package-6   | Removed    | 0.1.1 | -      |
| a/package-7   | Downgraded | 1.2.0 | 1.0.0  |


OUTPUT
                ,
                array(
                    '--no-dev' => null,
                    '-f' => 'mdtable',
                ),
            ),
            'Markdown with URLs' => array(
                <<<OUTPUT
| Prod Packages              | Operation  | Base  | Target | Link                                        |
|----------------------------|------------|-------|--------|---------------------------------------------|
| [a/package-1](github.com)  | New        | -     | 1.0.0  | [Compare](github.com/releases/tag/1.0.0)    |
| [a/package-2](github.com)  | Upgraded   | 1.0.0 | 1.2.0  | [Compare](github.com/compare/1.0.0...1.2.0) |
| [a/package-3](github.com)  | Removed    | 0.1.1 | -      | [Compare](github.com/releases/tag/0.1.1)    |
| [a/package-4](gitlab.org)  | Removed    | 0.1.1 | -      | [Compare](gitlab.org/tags/0.1.1)            |
| [a/package-5](gitlab2.org) | Removed    | 0.1.1 | -      | [Compare](gitlab2.org/tags/0.1.1)           |
| a/package-6                | Removed    | 0.1.1 | -      |                                             |
| [a/package-7](github.com)  | Downgraded | 1.2.0 | 1.0.0  | [Compare](github.com/compare/1.2.0...1.0.0) |


OUTPUT
            ,
                array(
                    '--no-dev' => null,
                    '-l' => null,
                    '-f' => 'anything',
                ),
            ),
            'Markdown with URLs and custom gitlab domains' => array(
                <<<OUTPUT
| Prod Packages              | Operation  | Base  | Target | Link                                        |
|----------------------------|------------|-------|--------|---------------------------------------------|
| [a/package-1](github.com)  | New        | -     | 1.0.0  | [Compare](github.com/releases/tag/1.0.0)    |
| [a/package-2](github.com)  | Upgraded   | 1.0.0 | 1.2.0  | [Compare](github.com/compare/1.0.0...1.2.0) |
| [a/package-3](github.com)  | Removed    | 0.1.1 | -      | [Compare](github.com/releases/tag/0.1.1)    |
| [a/package-4](gitlab.org)  | Removed    | 0.1.1 | -      | [Compare](gitlab.org/tags/0.1.1)            |
| [a/package-5](gitlab2.org) | Removed    | 0.1.1 | -      | [Compare](gitlab2.org/tags/0.1.1)           |
| [a/package-6](gitlab3.org) | Removed    | 0.1.1 | -      | [Compare](gitlab3.org/tags/0.1.1)           |
| [a/package-7](github.com)  | Downgraded | 1.2.0 | 1.0.0  | [Compare](github.com/compare/1.2.0...1.0.0) |


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
 - Downgrade a/package-7 (1.2.0 => 1.0.0)


OUTPUT
            ,
                array(
                    '--no-dev' => null,
                    '-f' => 'mdlist',
                ),
            ),
            'JSON' => array(
                json_encode(
                    array(
                    'packages' => array(
                            'a/package-1' => array(
                                    'name' => 'a/package-1',
                                    'operation' => 'install',
                                    'version_base' => null,
                                    'version_target' => '1.0.0',
                                ),
                            'a/package-2' => array(
                                    'name' => 'a/package-2',
                                    'operation' => 'upgrade',
                                    'version_base' => '1.0.0',
                                    'version_target' => '1.2.0',
                                ),
                            'a/package-3' => array(
                                    'name' => 'a/package-3',
                                    'operation' => 'remove',
                                    'version_base' => '0.1.1',
                                    'version_target' => null,
                                ),
                            'a/package-4' => array(
                                    'name' => 'a/package-4',
                                    'operation' => 'remove',
                                    'version_base' => '0.1.1',
                                    'version_target' => null,
                                ),
                            'a/package-5' => array(
                                    'name' => 'a/package-5',
                                    'operation' => 'remove',
                                    'version_base' => '0.1.1',
                                    'version_target' => null,
                                ),
                            'a/package-6' => array(
                                    'name' => 'a/package-6',
                                    'operation' => 'remove',
                                    'version_base' => '0.1.1',
                                    'version_target' => null,
                                ),
                            'a/package-7' => array(
                                'name' => 'a/package-7',
                                'operation' => 'downgrade',
                                'version_base' => '1.2.0',
                                'version_target' => '1.0.0',
                                ),
                        ),
                    'packages-dev' => array(
                        ),
                ),
                    128
                ).PHP_EOL,
                array(
                    '--no-dev' => null,
                    '-f' => 'json',
                ),
            ),
            'GitHub' => array(
                <<<OUTPUT
::notice title=Prod Packages:: - Install a/package-1 (1.0.0)%0A - Upgrade a/package-2 (1.0.0 => 1.2.0)%0A - Uninstall a/package-3 (0.1.1)%0A - Uninstall a/package-4 (0.1.1)%0A - Uninstall a/package-5 (0.1.1)%0A - Uninstall a/package-6 (0.1.1)%0A - Downgrade a/package-7 (1.2.0 => 1.0.0)

OUTPUT
                ,
                array(
                    '--no-dev' => null,
                    '-f' => 'github',
                ),
            ),
        );
    }
}
