<?php

namespace IonBazan\ComposerDiff\Tests;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\AliasPackage;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use Composer\Repository\ArrayRepository;
use Composer\Repository\RepositoryInterface;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Diff\EffectivePlatformPackage;
use IonBazan\ComposerDiff\PackageDiff;

class PackageDiffTest extends TestCase
{
    /**
     * @param string[] $expected
     *
     * @dataProvider operationsProvider
     */
    public function testBasicUsage(array $expected, bool $dev, bool $withPlatform, bool $onlyDirect = false): void
    {
        $diff = new PackageDiff();
        $operations = $diff->getPackageDiff(
            __DIR__.'/fixtures/base/composer.lock',
            __DIR__.'/fixtures/target/composer.lock',
            $dev,
            $withPlatform,
            $onlyDirect
        );

        $this->assertSame($expected, array_map([$this, 'entryToString'], $operations->getArrayCopy()));
    }

    public function testBasicUsageWithDefaultArguments(): void
    {
        $diff = new PackageDiff();
        $operations = $diff->getPackageDiff(
            __DIR__.'/fixtures/base/composer.lock',
            __DIR__.'/fixtures/target/composer.lock',
            false,
            true
        );

        $this->assertSame([
            'install psr/event-dispatcher 1.0.0',
            'update roave/security-advisories from dev-master to dev-master',
            'install symfony/deprecation-contracts v2.1.2',
            'update symfony/event-dispatcher from v2.8.52 to v5.1.2',
            'install symfony/event-dispatcher-contracts v2.1.2',
            'install symfony/polyfill-php80 v1.17.1',
            'install php >=5.3',
            'update php (effective) from >=7.2 <8.0 to >=7.2.5 <8.0',
        ], array_map([$this, 'entryToString'], $operations->getArrayCopy()));
    }

    public function testSameBaseAndTarget(): void
    {
        $diff = new PackageDiff();
        $operations = $diff->getPackageDiff(
            __DIR__.'/fixtures/base/composer.lock',
            __DIR__.'/fixtures/base/composer.lock',
            true,
            true
        );

        $this->assertCount(0, $operations);
    }

    /**
     * @param string[] $expected
     *
     * @dataProvider diffOperationsProvider
     */
    public function testDiff(array $expected, RepositoryInterface $oldRepository, RepositoryInterface $newRepository): void
    {
        $diff = new PackageDiff();
        $operations = $diff->getDiff($oldRepository, $newRepository);

        $this->assertSame($expected, array_map([$this, 'entryToString'], $operations->getArrayCopy()));
    }

    /**
     * @param string[] $expected
     *
     * @dataProvider diffOperationsProvider
     */
    public function testGetOperations(array $expected, RepositoryInterface $oldRepository, RepositoryInterface $newRepository): void
    {
        $diff = new PackageDiff();
        $operations = $diff->getOperations($oldRepository, $newRepository);

        $this->assertSame($expected, array_map([$this, 'operationToString'], $operations));
    }

    /**
     * @dataProvider diffOperationsProvider
     */
    public function testLoadFromArray(): void
    {
        $diff = new PackageDiff();

        $this->assertCount(1, $diff->loadPackagesFromArray(['platform-dev' => ['php' => '>=5.3']], true, true)->getPackages());
        $this->assertCount(1, $diff->loadPackagesFromArray(['platform' => ['php' => '>=5.3']], false, true)->getPackages());
        $this->assertCount(0, $diff->loadPackagesFromArray(['platform' => ['php' => '>=5.3']], true, true)->getPackages());
        $this->assertCount(0, $diff->loadPackagesFromArray(['platform-dev' => ['php' => '>=5.3']], false, true)->getPackages());
    }

    public function testLoadFromArrayAddsEffectivePlatformRequirements(): void
    {
        $lock = [
            'platform' => ['PHP' => '>=7.4.3', 'ext-json' => '*'],
            'packages' => [
                ['name' => 'a/package-a', 'version' => '1.0.0', 'require' => ['php' => '^7.2 || ^8.0', 'EXT-JSON' => '^1.5', 'a/package-b' => '^1.0']],
                ['name' => 'a/package-b', 'version' => '1.0.0', 'require' => ['ext-json' => '<1.9', 'ext-pdo' => '*', 'lib-icu' => '<60 || >=64', 'composer-plugin-api' => '^2.0']],
                ['name' => 'a/package-c', 'version' => '1.0.0'],
            ],
            'platform-dev' => ['php' => '~7.4.1'],
            'packages-dev' => [
                ['name' => 'a/package-d', 'version' => '1.0.0', 'require' => ['php' => '>7.0 <=8.1.2.3', 'ext-xdebug' => '>=3.0.0-beta1-dev']],
            ],
        ];
        $diff = new PackageDiff();

        $this->assertSame([
            'a/package-a 1.0.0',
            'a/package-b 1.0.0',
            'a/package-c 1.0.0',
            'php >=7.4.3',
            'ext-json *',
            'php (effective) >=7.4.3 <9.0',
            'ext-json (effective) >=1.5 <1.9',
            'ext-pdo (effective) *',
            'lib-icu (effective) <60.0 || >=64.0',
            'composer-plugin-api (effective) >=2.0 <3.0',
        ], $this->getPrettyVersions($diff->loadPackagesFromArray($lock, false, true)));
        $this->assertSame([
            'a/package-d 1.0.0',
            'php ~7.4.1',
            'php (effective) >=7.4.3 <7.5',
            'ext-json (effective) >=1.5 <1.9',
            'ext-pdo (effective) *',
            'lib-icu (effective) <60.0 || >=64.0',
            'composer-plugin-api (effective) >=2.0 <3.0',
            'ext-xdebug (effective) >=3.0-beta1-dev',
        ], $this->getPrettyVersions($diff->loadPackagesFromArray($lock, true, true)));
        $this->assertSame(['a/package-d 1.0.0'], $this->getPrettyVersions($diff->loadPackagesFromArray($lock, true, false)));
    }

    public function testDevDiffCombinesProdAndDevPlatformRequirements(): void
    {
        $diff = new PackageDiff();

        $this->assertSame([
            'update php (effective) from >=7.2 to >=8.0',
            'install ext-intl (effective) *',
        ], array_map([$this, 'entryToString'], $diff->getPackageDiff(__DIR__.'/fixtures/platform-base/composer.lock', __DIR__.'/fixtures/platform-target/composer.lock', false, true)->getArrayCopy()));
        $this->assertSame([
            'update php (effective) from >=7.2 to >=8.0',
            'install ext-intl (effective) *',
            'install ext-xdebug (effective) *',
        ], array_map([$this, 'entryToString'], $diff->getPackageDiff(__DIR__.'/fixtures/platform-base/composer.lock', __DIR__.'/fixtures/platform-target/composer.lock', true, true)->getArrayCopy()));
    }

    public function testLoadFromArraySkipsProvidedPlatformRequirements(): void
    {
        $lock = [
            'platform' => ['ext-mbstring' => '*'],
            'packages' => [
                ['name' => 'symfony/polyfill-mbstring', 'version' => '1.0.0', 'provide' => ['EXT-MBSTRING' => '*']],
                ['name' => 'a/replacement', 'version' => '1.0.0', 'replace' => ['ext-foo' => '*']],
                ['name' => 'a/package', 'version' => '1.0.0', 'require' => ['ext-mbstring' => '*', 'ext-foo' => '*', 'ext-bar' => '*']],
            ],
        ];

        $this->assertSame([
            'symfony/polyfill-mbstring 1.0.0',
            'a/replacement 1.0.0',
            'a/package 1.0.0',
            'ext-mbstring *',
            'ext-bar (effective) *',
        ], $this->getPrettyVersions((new PackageDiff())->loadPackagesFromArray($lock, false, true)));
    }

    public function testLoadFromArrayShowsUnresolvablePlatformRequirements(): void
    {
        $lock = [
            'platform' => ['php' => '^7.0'],
            'packages' => [
                ['name' => 'a/package-a', 'version' => '1.0.0', 'require' => ['php' => '^8.0', 'ext-foo' => 'dev-main']],
                ['name' => 'a/package-b', 'version' => '1.0.0', 'require' => ['php' => '^8.0', 'lib-foo' => '>1.0 <=2.0.1']],
            ],
        ];

        $this->assertSame([
            'a/package-a 1.0.0',
            'a/package-b 1.0.0',
            'php ^7.0',
            'php (effective) conflicting (2 constraints)',
            'ext-foo (effective) dev-main',
            'lib-foo (effective) >1.0 <=2.0.1',
        ], $this->getPrettyVersions((new PackageDiff())->loadPackagesFromArray($lock, false, true)));
    }

    /**
     * @param string[] $expected
     *
     * @dataProvider operationsProvider
     */
    public function testGitUsage(array $expected, bool $dev, bool $withPlatform, bool $onlyDirect = false): void
    {
        $diff = new PackageDiff();
        $this->prepareGit();
        $operations = $diff->getPackageDiff('HEAD', '', $dev, $withPlatform, $onlyDirect);

        $this->assertSame($expected, array_map([$this, 'entryToString'], $operations->getArrayCopy()));
    }

    /**
     * @param string[] $expected
     *
     * @dataProvider operationsProvider
     */
    public function testGitUsageWithoutJson(array $expected, bool $dev, bool $withPlatform, bool $onlyDirect = false): void
    {
        $diff = new PackageDiff();
        $this->prepareGit(true);
        $operations = $diff->getPackageDiff('HEAD', '', $dev, $withPlatform, $onlyDirect);

        if ($onlyDirect) {
            $expected = []; // if there is no json file, we can't determine direct dependencies
        }

        $this->assertSame($expected, array_map([$this, 'entryToString'], $operations->getArrayCopy()));
    }

    public function testInvalidGitRef(): void
    {
        $diff = new PackageDiff();
        $this->prepareGit();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('{^Could not open file invalid-ref or find it in git as invalid-ref:\./composer\.lock: \S.*\S\z}s');
        $diff->getPackageDiff('invalid-ref', '', true, true);
    }

    public function testMissingLocalFileThrowsByDefault(): void
    {
        $diff = new PackageDiff();
        $this->expectException(\RuntimeException::class);
        $diff->getPackageDiff(__DIR__.'/fixtures/nonexistent/composer.lock', __DIR__.'/fixtures/target/composer.lock', false, false);
    }

    public function testMissingLocalFileAllowed(): void
    {
        $diff = new PackageDiff();
        $operations = $diff->getPackageDiff(__DIR__.'/fixtures/nonexistent/composer.lock', __DIR__.'/fixtures/target/composer.lock', false, false, false, true);

        foreach ($operations as $entry) {
            $this->assertTrue($entry->isInstall(), 'All entries should be installs when base is missing');
        }
        $this->assertNotCount(0, $operations);
    }

    public function testMissingGitRefAllowed(): void
    {
        $diff = new PackageDiff();
        $this->prepareGit();
        $operations = $diff->getPackageDiff('HEAD:nonexistent/composer.lock', '', false, false, false, true);

        foreach ($operations as $entry) {
            $this->assertTrue($entry->isInstall(), 'All entries should be installs when base ref is missing');
        }
        $this->assertNotCount(0, $operations);
    }

    public function testGitUsageFromSubdirectory(): void
    {
        $diff = new PackageDiff();
        $this->prepareGit(false, 'sub');
        file_put_contents(__DIR__.'/test-git/composer.lock', '{}');
        exec('git add composer.lock && git commit -m "add root lock"');
        chdir(__DIR__.'/test-git/sub');

        $this->assertSame(
            'update phpunit/phpunit from 9.2.5 to 8.5.8',
            $this->entryToString($diff->getPackageDiff('HEAD', '', true, false, true)[0])
        );
    }

    public function testGitWarningIsNotPartOfFileContents(): void
    {
        $diff = new PackageDiff();
        $this->prepareGit();
        $branch = trim((string) exec('git rev-parse --abbrev-ref HEAD'));
        exec('git tag '.$branch);

        $this->assertCount(20, $diff->getPackageDiff($branch, '', true, false));
    }

    public function testInvalidLockFile(): void
    {
        $diff = new PackageDiff();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not parse '.__DIR__.'/fixtures/invalid/composer.lock as JSON: Syntax error');
        $diff->getPackageDiff(__DIR__.'/fixtures/invalid/composer.lock', __DIR__.'/fixtures/target/composer.lock', false, false);
    }

    public function testInvalidJsonFile(): void
    {
        $diff = new PackageDiff();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not parse '.__DIR__.'/fixtures/invalid-json/composer.lock as JSON: Syntax error');
        $diff->getPackageDiff(__DIR__.'/fixtures/invalid-json/composer.lock', __DIR__.'/fixtures/target/composer.lock', false, false);
    }

    public function testUnreadableUrl(): void
    {
        $diff = new PackageDiff();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not read http://127.0.0.1:1/composer.lock');
        $diff->getPackageDiff('http://127.0.0.1:1/composer.lock', __DIR__.'/fixtures/target/composer.lock', false, false);
    }

    public function testDirectPackagesAreCaseInsensitive(): void
    {
        $diff = new PackageDiff();
        $operations = $diff->getPackageDiff(__DIR__.'/fixtures/uppercase/base/composer.lock', __DIR__.'/fixtures/uppercase/target/composer.lock', true, false, true);

        $this->assertSame(['update phpunit/phpunit from 9.2.5 to 8.5.8'], array_map([$this, 'entryToString'], $operations->getArrayCopy()));
    }

    public function testLoadFromEmptyArray(): void
    {
        $diff = new PackageDiff();

        $this->assertInstanceOf(ArrayRepository::class, $diff->loadPackagesFromArray([], false, true));
        $this->assertInstanceOf(ArrayRepository::class, $diff->loadPackagesFromArray([], true, true));
    }

    /**
     * @return iterable<array<mixed>>
     */
    public function diffOperationsProvider(): iterable
    {
        return [
            'update alias version' => [
                [],
                new ArrayRepository([
                    new AliasPackage(new Package('vendor/package-a', '1.0', '1.0'), '1.0', '1.0'),
                ]),
                new ArrayRepository([
                    new AliasPackage(new Package('vendor/package-a', '1.0', '1.0'), '2.0', '2.0'),
                ]),
            ],
            'same alias version but different actual package version' => [
                [
                    'update vendor/package-a from 1.0 to 2.0',
                ],
                new ArrayRepository([
                    new AliasPackage(new Package('vendor/package-a', '1.0', '1.0'), '1.0', '1.0'),
                ]),
                new ArrayRepository([
                    new AliasPackage(new Package('vendor/package-a', '2.0', '2.0'), '1.0', '1.0'),
                ]),
            ],
            'uninstall aliased package' => [
                [
                    'uninstall vendor/package-a 1.0',
                ],
                new ArrayRepository([
                    new AliasPackage(new Package('vendor/package-a', '1.0', '1.0'), '2.0', '2.0'),
                ]),
                new ArrayRepository([
                ]),
            ],
            'add aliased package' => [
                [
                    'install vendor/package-a 1.0',
                ],
                new ArrayRepository([]),
                new ArrayRepository([
                    new AliasPackage(new Package('vendor/package-a', '1.0', '1.0'), '2.0', '2.0'),
                ]),
            ],
        ];
    }

    /**
     * @return iterable<array<mixed>>
     */
    public function operationsProvider(): iterable
    {
        return [
            'prod, with platform' => [
                'expected' => [
                    'install psr/event-dispatcher 1.0.0',
                    'update roave/security-advisories from dev-master to dev-master',
                    'install symfony/deprecation-contracts v2.1.2',
                    'update symfony/event-dispatcher from v2.8.52 to v5.1.2',
                    'install symfony/event-dispatcher-contracts v2.1.2',
                    'install symfony/polyfill-php80 v1.17.1',
                    'install php >=5.3',
                    'update php (effective) from >=7.2 <8.0 to >=7.2.5 <8.0',
                ],
                'dev' => false,
                'withPlatform' => true,
            ],
            'prod, no platform' => [
                'expected' => [
                    'install psr/event-dispatcher 1.0.0',
                    'update roave/security-advisories from dev-master to dev-master',
                    'install symfony/deprecation-contracts v2.1.2',
                    'update symfony/event-dispatcher from v2.8.52 to v5.1.2',
                    'install symfony/event-dispatcher-contracts v2.1.2',
                    'install symfony/polyfill-php80 v1.17.1',
                ],
                'dev' => false,
                'withPlatform' => false,
            ],
            'dev, no platform' => [
                'expected' => [
                    'update phpunit/php-code-coverage from 8.0.2 to 7.0.10',
                    'update phpunit/php-file-iterator from 3.0.2 to 2.0.2',
                    'update phpunit/php-text-template from 2.0.1 to 1.2.1',
                    'update phpunit/php-timer from 5.0.0 to 2.1.2',
                    'update phpunit/php-token-stream from 4.0.2 to 3.1.1',
                    'update phpunit/phpunit from 9.2.5 to 8.5.8',
                    'update sebastian/code-unit-reverse-lookup from 2.0.1 to 1.0.1',
                    'update sebastian/comparator from 4.0.2 to 3.0.2',
                    'update sebastian/diff from 4.0.1 to 3.0.2',
                    'update sebastian/environment from 5.1.1 to 4.2.3',
                    'update sebastian/exporter from 4.0.1 to 3.1.2',
                    'update sebastian/global-state from 4.0.0 to 3.0.0',
                    'update sebastian/object-enumerator from 4.0.1 to 3.0.3',
                    'update sebastian/object-reflector from 2.0.1 to 1.1.1',
                    'update sebastian/recursion-context from 4.0.1 to 3.0.0',
                    'update sebastian/resource-operations from 3.0.1 to 2.0.1',
                    'update sebastian/type from 2.1.0 to 1.1.3',
                    'update sebastian/version from 3.0.0 to 2.0.1',
                    'uninstall phpunit/php-invoker 3.0.1',
                    'uninstall sebastian/code-unit 1.0.3',
                ],
                'dev' => true,
                'withPlatform' => false,
            ],
            'prod, only direct' => [
                'expected' => [
                    'update roave/security-advisories from dev-master to dev-master',
                    'update symfony/event-dispatcher from v2.8.52 to v5.1.2',
                ],
                'dev' => false,
                'withPlatform' => false,
                'onlyDirect' => true,
            ],
            'dev, only direct' => [
                'expected' => [
                    'update phpunit/phpunit from 9.2.5 to 8.5.8',
                ],
                'dev' => true,
                'withPlatform' => false,
                'onlyDirect' => true,
            ],
        ];
    }

    private function prepareGit(bool $onlyLock = false, string $subdirectory = ''): void
    {
        $gitDir = __DIR__.'/test-git';
        // Keeps git from falling back to this package's own repository when test-git has no .git yet
        putenv('GIT_CEILING_DIRECTORIES='.__DIR__);
        $this->removeDirectory($gitDir);
        mkdir($gitDir);
        chdir($gitDir);
        exec('git init');
        exec('git config user.name test');
        exec('git config user.email test@example.com');
        $projectDir = $gitDir.('' !== $subdirectory ? '/'.$subdirectory : '');
        @mkdir($projectDir);
        file_put_contents($projectDir.'/composer.lock', file_get_contents(__DIR__.'/fixtures/base/composer.lock'));
        !$onlyLock && file_put_contents($projectDir.'/composer.json', file_get_contents(__DIR__.'/fixtures/base/composer.json'));
        exec('git add -A && git commit -m "init"');
        file_put_contents($projectDir.'/composer.lock', file_get_contents(__DIR__.'/fixtures/target/composer.lock'));
        !$onlyLock && file_put_contents($projectDir.'/composer.json', file_get_contents(__DIR__.'/fixtures/target/composer.json'));
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            @chmod($file->getPathname(), 0777);
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($dir);
    }

    /**
     * @return string[]
     */
    private function getPrettyVersions(RepositoryInterface $repository): array
    {
        return array_map(function (PackageInterface $package): string {
            return $this->getLabel($package).' '.$package->getPrettyVersion();
        }, $repository->getPackages());
    }

    private function getLabel(PackageInterface $package): string
    {
        return $package->getName().($package instanceof EffectivePlatformPackage ? ' (effective)' : '');
    }

    private function entryToString(DiffEntry $entry): string
    {
        return $this->operationToString($entry->getOperation());
    }

    private function operationToString(OperationInterface $operation): string
    {
        if ($operation instanceof InstallOperation) {
            return sprintf('install %s %s', $this->getLabel($operation->getPackage()), $operation->getPackage()->getPrettyVersion());
        }

        if ($operation instanceof UpdateOperation) {
            return sprintf('update %s from %s to %s', $this->getLabel($operation->getInitialPackage()), $operation->getInitialPackage()->getPrettyVersion(), $operation->getTargetPackage()->getPrettyVersion());
        }

        if ($operation instanceof UninstallOperation) {
            return sprintf('uninstall %s %s', $this->getLabel($operation->getPackage()), $operation->getPackage()->getPrettyVersion());
        }

        throw new \InvalidArgumentException('Invalid operation provided');
    }
}
