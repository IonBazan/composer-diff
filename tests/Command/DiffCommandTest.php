<?php

namespace IonBazan\ComposerDiff\Tests\Command;

use IonBazan\ComposerDiff\PackageDiff;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use IonBazan\ComposerDiff\Command\DiffCommand;
use IonBazan\ComposerDiff\Tests\TestCase;
use IonBazan\ComposerDiff\Url\GeneratorContainer;
use Symfony\Component\Console\Tester\CommandTester;

class DiffCommandTest extends TestCase
{
    /**
     * @dataProvider outputDataProvider
     *
     * @param array<string, mixed> $options
     */
    public function testItGeneratesReportInGivenFormat(string $expectedOutput, array $options): void
    {
        $diff = $this->getMockBuilder(PackageDiff::class)->getMock();
        $application = $this->getComposerApplication();
        $command = new DiffCommand($diff, ['gitlab2.org']);
        $command->setApplication($application);
        $tester = new CommandTester($command);
        $diff->expects($this->once())
            ->method('getPackageDiff')
            ->with($this->isType('string'), $this->isType('string'), false, false)
            ->willReturn($this->getEntries([
                new InstallOperation($this->getPackageWithSource('a/package-1', '1.0.0', 'github.com')),
                new UpdateOperation($this->getPackageWithSource('a/package-2', '1.0.0', 'github.com'), $this->getPackageWithSource('a/package-2', '1.2.0', 'github.com')),
                new UninstallOperation($this->getPackageWithSource('a/package-3', '0.1.1', 'github.com')),
                new UninstallOperation($this->getPackageWithSource('a/package-4', '0.1.1', 'gitlab.org')),
                new UninstallOperation($this->getPackageWithSource('a/package-5', '0.1.1', 'gitlab2.org')),
                new UninstallOperation($this->getPackageWithSource('a/package-6', '0.1.1', 'gitlab3.org')),
                new UpdateOperation($this->getPackageWithSource('a/package-7', '1.2.0', 'github.com'), $this->getPackageWithSource('a/package-7', '1.0.0', 'github.com')),
            ],
                new GeneratorContainer(['gitlab2.org'])
            ))
        ;
        $result = $tester->execute($options);
        $this->assertSame(0, $result);
        $this->assertSame($expectedOutput, $tester->getDisplay());
    }

    public function testFilterOption(): void
    {
        $diff = $this->getMockBuilder(PackageDiff::class)->getMock();
        $application = $this->getComposerApplication();
        $command = new DiffCommand($diff);
        $command->setApplication($application);
        $tester = new CommandTester($command);
        $diff->expects($this->atLeast(1))
            ->method('getPackageDiff')
            ->willReturn($this->getEntries([
                new InstallOperation($this->getPackageWithSource('symfony/console', '6.0.0', 'github.com')),
                new InstallOperation($this->getPackageWithSource('doctrine/orm', '2.0.0', 'github.com')),
                new InstallOperation($this->getPackageWithSource('symfony/http-kernel', '6.0.0', 'github.com')),
            ], $this->getGenerators()))
        ;
        $tester->execute(['--filter' => ['symfony/*']]);
        $output = $tester->getDisplay();
        $this->assertStringContainsString('symfony/console', $output);
        $this->assertStringContainsString('symfony/http-kernel', $output);
        $this->assertStringNotContainsString('doctrine/orm', $output);
    }

    public function testSortByName(): void
    {
        $diff = $this->getMockBuilder(PackageDiff::class)->getMock();
        $application = $this->getComposerApplication();
        $command = new DiffCommand($diff);
        $command->setApplication($application);
        $tester = new CommandTester($command);
        $diff->expects($this->atLeast(1))
            ->method('getPackageDiff')
            ->willReturn($this->getEntries([
                new InstallOperation($this->getPackageWithSource('symfony/console', '6.0.0', 'github.com')),
                new InstallOperation($this->getPackageWithSource('doctrine/orm', '2.0.0', 'github.com')),
                new InstallOperation($this->getPackageWithSource('symfony/http-kernel', '6.0.0', 'github.com')),
            ], $this->getGenerators()))
        ;
        $tester->execute(['--sort' => null]);
        $output = $tester->getDisplay();
        $pos1 = strpos($output, 'doctrine/orm');
        $pos2 = strpos($output, 'symfony/console');
        $pos3 = strpos($output, 'symfony/http-kernel');
        $this->assertNotFalse($pos1);
        $this->assertNotFalse($pos2);
        $this->assertNotFalse($pos3);
        $this->assertLessThan($pos2, $pos1);
        $this->assertLessThan($pos3, $pos2);
    }

    public function testSortByOperation(): void
    {
        $diff = $this->getMockBuilder(PackageDiff::class)->getMock();
        $application = $this->getComposerApplication();
        $command = new DiffCommand($diff);
        $command->setApplication($application);
        $tester = new CommandTester($command);
        $diff->expects($this->atLeast(1))
            ->method('getPackageDiff')
            ->willReturn($this->getEntries([
                new UninstallOperation($this->getPackageWithSource('b/package', '1.0.0', 'github.com')),
                new InstallOperation($this->getPackageWithSource('a/package', '2.0.0', 'github.com')),
            ], $this->getGenerators()))
        ;
        $tester->execute(['--sort' => 'operation']);
        $output = $tester->getDisplay();
        $installPos = strpos($output, 'a/package');
        $removePos = strpos($output, 'b/package');
        $this->assertNotFalse($installPos);
        $this->assertNotFalse($removePos);
        $this->assertLessThan($removePos, $installPos);
    }

    public function testMultipleFilterPatterns(): void
    {
        $diff = $this->getMockBuilder(PackageDiff::class)->getMock();
        $application = $this->getComposerApplication();
        $command = new DiffCommand($diff);
        $command->setApplication($application);
        $tester = new CommandTester($command);
        $diff->expects($this->atLeast(1))
            ->method('getPackageDiff')
            ->willReturn($this->getEntries([
                new InstallOperation($this->getPackageWithSource('symfony/console', '6.0.0', 'github.com')),
                new InstallOperation($this->getPackageWithSource('doctrine/orm', '2.0.0', 'github.com')),
                new InstallOperation($this->getPackageWithSource('twig/twig', '3.0.0', 'github.com')),
            ], $this->getGenerators()))
        ;
        $tester->execute(['--filter' => ['symfony/*', 'doctrine/*']]);
        $output = $tester->getDisplay();
        $this->assertStringContainsString('symfony/console', $output);
        $this->assertStringContainsString('doctrine/orm', $output);
        $this->assertStringNotContainsString('twig/twig', $output);
    }

    public function testExtraGitlabDomains(): void
    {
        $diff = $this->getMockBuilder(PackageDiff::class)->getMock();
        $application = $this->getComposerApplication();
        $command = new DiffCommand($diff, ['gitlab2.org']);
        $command->setApplication($application);
        $tester = new CommandTester($command);

        $packages = [
            $this->getPackageWithSource('a/package-1', '1.0.0', 'github.com'),
            $this->getPackageWithSource('a/package-4', '0.1.1', 'gitlab.org'),
            $this->getPackageWithSource('a/package-5', '0.1.1', 'gitlab2.org'),
            $this->getPackageWithSource('a/package-6', '0.1.1', 'gitlab3.org'),
            $this->getPackageWithSource('a/package-7', '1.2.0', 'github.com'),
        ];

        $diff->expects($this->atLeast(1))
            ->method('getPackageDiff')
            ->willReturn($this->getEntries([], $this->getGenerators()));

        $diff->expects($this->once())
            ->method('setUrlGenerator')
            ->with($this->callback(function (GeneratorContainer $argument) use ($packages): bool {
                foreach ($packages as $package) {
                    if (!$argument->supportsPackage($package)) {
                        return false;
                    }
                }

                return true;
            }));
        $result = $tester->execute(['--gitlab-domains' => ['gitlab3.org']]);
        $this->assertSame(0, $result);
    }

    /**
     * @param OperationInterface[] $prodOperations
     * @param OperationInterface[] $devOperations
     *
     * @dataProvider strictDataProvider
     */
    public function testStrictMode(int $exitCode, array $prodOperations, array $devOperations): void
    {
        $diff = $this->getMockBuilder(PackageDiff::class)->getMock();
        $application = $this->getComposerApplication();
        $command = new DiffCommand($diff, ['gitlab2.org']);
        $command->setApplication($application);
        $tester = new CommandTester($command);
        $diff->expects($this->exactly(2))
            ->method('getPackageDiff')
            ->with($this->isType('string'), $this->isType('string'), $this->isType('boolean'), false)
            ->willReturnOnConsecutiveCalls(
                $this->getEntries($prodOperations, $this->getGenerators()),
                $this->getEntries($devOperations, $this->getGenerators())
            )
        ;
        $this->assertSame($exitCode, $tester->execute(['--strict' => null]));
    }

    /**
     * @return iterable<array<mixed>>
     */
    public function strictDataProvider(): iterable
    {
        return [
            'No changes' => [0, [], []],
            'Changes in prod and dev' => [
                6,
                [
                    new InstallOperation($this->getPackageWithSource('a/package-1', '1.0.0', 'github.com')),
                    new UpdateOperation($this->getPackageWithSource('a/package-2', '1.0.0', 'github.com'), $this->getPackageWithSource('a/package-2', '1.2.0', 'github.com')),
                ],
                [
                    new UpdateOperation($this->getPackageWithSource('a/package-3', '1.0.0', 'github.com'), $this->getPackageWithSource('a/package-3', '1.2.0', 'github.com')),
                    new InstallOperation($this->getPackageWithSource('a/package-4', '1.0.0', 'github.com')),
                ],
            ],
            'Downgrades in prod and changes in dev' => [
                14,
                [
                    new InstallOperation($this->getPackageWithSource('a/package-1', '1.0.0', 'github.com')),
                    new UpdateOperation($this->getPackageWithSource('a/package-2', '1.2.0', 'github.com'), $this->getPackageWithSource('a/package-2', '1.0.0', 'github.com')),
                ],
                [
                    new UpdateOperation($this->getPackageWithSource('a/package-3', '1.0.0', 'github.com'), $this->getPackageWithSource('a/package-3', '1.2.0', 'github.com')),
                    new InstallOperation($this->getPackageWithSource('a/package-4', '1.0.0', 'github.com')),
                ],
            ],
            'Changes in prod and downgrades in dev' => [
                22,
                [
                    new InstallOperation($this->getPackageWithSource('a/package-1', '1.0.0', 'github.com')),
                    new UpdateOperation($this->getPackageWithSource('a/package-2', '1.0.0', 'github.com'), $this->getPackageWithSource('a/package-2', '1.2.0', 'github.com')),
                ],
                [
                    new UpdateOperation($this->getPackageWithSource('a/package-3', '1.2.0', 'github.com'), $this->getPackageWithSource('a/package-3', '1.0.0', 'github.com')),
                    new InstallOperation($this->getPackageWithSource('a/package-4', '1.0.0', 'github.com')),
                ],
            ],
            'Downgrades in both' => [
                30,
                [
                    new InstallOperation($this->getPackageWithSource('a/package-1', '1.0.0', 'github.com')),
                    new UpdateOperation($this->getPackageWithSource('a/package-2', '1.2.0', 'github.com'), $this->getPackageWithSource('a/package-2', '1.0.0', 'github.com')),
                ],
                [
                    new UpdateOperation($this->getPackageWithSource('a/package-3', '1.2.0', 'github.com'), $this->getPackageWithSource('a/package-3', '1.0.0', 'github.com')),
                    new InstallOperation($this->getPackageWithSource('a/package-4', '1.0.0', 'github.com')),
                ],
            ],
        ];
    }

    /**
     * @return iterable<array<mixed>>
     */
    public function outputDataProvider(): iterable
    {
        return [
            'Markdown table' => [
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
                [
                    '--no-dev' => null,
                    '-f' => 'mdtable',
                ],
            ],
            'Markdown with URLs' => [
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
                [
                    '--no-dev' => null,
                    '-l' => null,
                    '-f' => 'anything',
                ],
            ],
            'Markdown list' => [
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
                [
                    '--no-dev' => null,
                    '-f' => 'mdlist',
                ],
            ],
            'JSON' => [
                json_encode(
                    [
                    'packages' => [
                            'a/package-1' => [
                                    'name' => 'a/package-1',
                                    'direct' => false,
                                    'operation' => 'install',
                                    'version_base' => null,
                                    'version_target' => '1.0.0',
                                ],
                            'a/package-2' => [
                                    'name' => 'a/package-2',
                                    'direct' => false,
                                    'operation' => 'upgrade',
                                    'version_base' => '1.0.0',
                                    'version_target' => '1.2.0',
                                ],
                            'a/package-3' => [
                                    'name' => 'a/package-3',
                                    'direct' => false,
                                    'operation' => 'remove',
                                    'version_base' => '0.1.1',
                                    'version_target' => null,
                                ],
                            'a/package-4' => [
                                    'name' => 'a/package-4',
                                    'direct' => false,
                                    'operation' => 'remove',
                                    'version_base' => '0.1.1',
                                    'version_target' => null,
                                ],
                            'a/package-5' => [
                                    'name' => 'a/package-5',
                                    'direct' => false,
                                    'operation' => 'remove',
                                    'version_base' => '0.1.1',
                                    'version_target' => null,
                                ],
                            'a/package-6' => [
                                    'name' => 'a/package-6',
                                    'direct' => false,
                                    'operation' => 'remove',
                                    'version_base' => '0.1.1',
                                    'version_target' => null,
                                ],
                            'a/package-7' => [
                                'name' => 'a/package-7',
                                'direct' => false,
                                'operation' => 'downgrade',
                                'version_base' => '1.2.0',
                                'version_target' => '1.0.0',
                                ],
                        ],
                    'packages-dev' => [
                        ],
                ],
                    JSON_PRETTY_PRINT
                ).PHP_EOL,
                [
                    '--no-dev' => null,
                    '-f' => 'json',
                ],
            ],
            'GitHub' => [
                <<<OUTPUT
::notice title=Prod Packages:: - Install a/package-1 (1.0.0)%0A - Upgrade a/package-2 (1.0.0 => 1.2.0)%0A - Uninstall a/package-3 (0.1.1)%0A - Uninstall a/package-4 (0.1.1)%0A - Uninstall a/package-5 (0.1.1)%0A - Uninstall a/package-6 (0.1.1)%0A - Downgrade a/package-7 (1.2.0 => 1.0.0)

OUTPUT
                ,
                [
                    '--no-dev' => null,
                    '-f' => 'github',
                ],
            ],
        ];
    }
}
