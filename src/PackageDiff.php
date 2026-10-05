<?php

namespace IonBazan\ComposerDiff;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\AliasPackage;
use Composer\Package\CompletePackage;
use Composer\Package\Loader\ArrayLoader;
use Composer\Package\PackageInterface;
use Composer\Repository\ArrayRepository;
use Composer\Repository\PlatformRepository;
use Composer\Repository\RepositoryInterface;
use Composer\Semver\Constraint\MultiConstraint;
use Composer\Semver\Interval;
use Composer\Semver\Intervals;
use Composer\Semver\VersionParser;
use Composer\Util\ProcessExecutor;
use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Diff\EffectivePlatformPackage;
use IonBazan\ComposerDiff\Url\GeneratorContainer;
use IonBazan\ComposerDiff\Url\UrlGenerator;

class PackageDiff
{
    const COMPOSER = 'composer';
    const EXTENSION_LOCK = '.lock';
    const EXTENSION_JSON = '.json';
    const GIT_SEPARATOR = ':';
    const CURRENT_DIRECTORY = './';

    /** @var UrlGenerator */
    protected $urlGenerator;

    public function __construct()
    {
        $this->urlGenerator = new GeneratorContainer();
    }

    public function setUrlGenerator(UrlGenerator $urlGenerator): void
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param string[] $directPackages
     */
    public function getDiff(RepositoryInterface $oldPackages, RepositoryInterface $targetPackages, array $directPackages = [], bool $onlyDirect = false): DiffEntries
    {
        $entries = [];

        foreach ($this->getOperations($oldPackages, $targetPackages) as $operation) {
            $package = $operation instanceof UpdateOperation ? $operation->getTargetPackage() : $operation->getPackage();
            $direct = !$package instanceof EffectivePlatformPackage && in_array($package->getName(), $directPackages, true);

            if ($onlyDirect && !$direct) {
                continue;
            }

            $entries[] = new DiffEntry($operation, $this->urlGenerator, $direct);
        }

        return new DiffEntries($entries);
    }

    /**
     * @return array<InstallOperation|UpdateOperation|UninstallOperation>
     */
    public function getOperations(RepositoryInterface $oldPackages, RepositoryInterface $targetPackages): array
    {
        $operations = [];

        foreach ($targetPackages->getPackages() as $newPackage) {
            $matchingPackages = $this->findMatchingPackages($oldPackages, $newPackage);

            if ($newPackage instanceof AliasPackage) {
                continue;
            }

            if (0 === count($matchingPackages)) {
                $operations[] = new InstallOperation($newPackage);

                continue;
            }

            foreach ($matchingPackages as $oldPackage) {
                if ($oldPackage instanceof AliasPackage) {
                    continue;
                }

                if ($oldPackage->getFullPrettyVersion() !== $newPackage->getFullPrettyVersion()) {
                    $operations[] = new UpdateOperation($oldPackage, $newPackage);
                }
            }
        }

        foreach ($oldPackages->getPackages() as $oldPackage) {
            if ($oldPackage instanceof AliasPackage) {
                continue;
            }

            if (!$this->findMatchingPackages($targetPackages, $oldPackage)) {
                $operations[] = new UninstallOperation($oldPackage);
            }
        }

        return $operations;
    }

    /**
     * @return PackageInterface[]
     */
    private function findMatchingPackages(RepositoryInterface $repository, PackageInterface $package): array
    {
        return array_filter($repository->findPackages($package->getName()), function (PackageInterface $candidate) use ($package): bool {
            return $candidate instanceof EffectivePlatformPackage === $package instanceof EffectivePlatformPackage;
        });
    }

    public function getPackageDiff(string $from, string $to, bool $dev, bool $withPlatform, bool $onlyDirect = false, bool $allowMissingFiles = false): DiffEntries
    {
        return $this->getDiff(
            $this->loadPackages($from, $dev, $withPlatform, $allowMissingFiles),
            $this->loadPackages($to, $dev, $withPlatform, $allowMissingFiles),
            array_merge($this->getDirectPackages($from), $this->getDirectPackages($to)),
            $onlyDirect
        );
    }

    /**
     * @param mixed[] $composerLock
     */
    public function loadPackagesFromArray(array $composerLock, bool $dev, bool $withPlatform): ArrayRepository
    {
        $loader = new ArrayLoader();
        $packages = [];
        $packagesKey = 'packages'.($dev ? '-dev' : '');

        if (isset($composerLock[$packagesKey])) {
            foreach ($composerLock[$packagesKey] as $packageInfo) {
                $packages[] = $loader->load($packageInfo);
            }
        }

        if ($withPlatform) {
            foreach ($composerLock['platform'.($dev ? '-dev' : '')] ?? [] as $name => $version) {
                $packages[] = new CompletePackage($name, $version, $version);
            }

            foreach ($this->getEffectiveRequirements($composerLock, $dev) as $name => $version) {
                $packages[] = new EffectivePlatformPackage($name, $version, $version);
            }
        }

        return new ArrayRepository($packages);
    }

    /**
     * @param mixed[] $composerLock
     *
     * @return array<string, string>
     */
    private function getEffectiveRequirements(array $composerLock, bool $dev): array
    {
        if (!$dev) {
            return $this->getEffectiveRanges($composerLock, ['platform'], ['packages']);
        }

        return $this->getEffectiveRanges($composerLock, ['platform', 'platform-dev'], ['packages', 'packages-dev']);
    }

    /**
     * @param mixed[]  $composerLock
     * @param string[] $rootKeys
     * @param string[] $packageKeys
     *
     * @return array<string, string>
     */
    private function getEffectiveRanges(array $composerLock, array $rootKeys, array $packageKeys): array
    {
        $requirements = [];
        $provided = [];

        foreach ($packageKeys as $packageKey) {
            foreach ($composerLock[$packageKey] ?? [] as $packageInfo) {
                foreach (array_merge($packageInfo['provide'] ?? [], $packageInfo['replace'] ?? []) as $name => $constraint) {
                    $provided[strtolower($name)] = $constraint;
                }

                foreach ($packageInfo['require'] ?? [] as $name => $constraint) {
                    if (PlatformRepository::isPlatformPackage($name)) {
                        $requirements[strtolower($name)][] = $constraint;
                    }
                }
            }
        }

        $requirements = array_diff_key($requirements, $provided);

        foreach ($rootKeys as $rootKey) {
            foreach ($composerLock[$rootKey] ?? [] as $name => $constraint) {
                if (isset($requirements[strtolower($name)])) {
                    $requirements[strtolower($name)][] = $constraint;
                }
            }
        }

        return array_map([$this, 'getEffectiveConstraint'], $requirements);
    }

    /**
     * @param string[] $constraints
     */
    private function getEffectiveConstraint(array $constraints): string
    {
        $parser = new VersionParser();
        $intervals = Intervals::get(MultiConstraint::create(array_map([$parser, 'parseConstraints'], $constraints)));
        $ranges = [];

        foreach ($intervals['numeric'] as $interval) {
            $range = [];

            if ($interval->getStart()->getVersion() !== Interval::fromZero()->getVersion()) {
                $range[] = $interval->getStart()->getOperator().$this->getPrettyVersion($interval->getStart()->getVersion());
            }

            if ($interval->getEnd()->getVersion() !== Interval::untilPositiveInfinity()->getVersion()) {
                $range[] = $interval->getEnd()->getOperator().$this->getPrettyVersion($interval->getEnd()->getVersion());
            }

            $ranges[] = $range ? implode(' ', $range) : '*';
        }

        if (!$ranges) {
            $ranges = $intervals['branches']['names'];
        }

        return $ranges ? implode(' || ', $ranges) : sprintf('conflicting (%d constraints)', count(array_unique($constraints)));
    }

    private function getPrettyVersion(string $version): string
    {
        $parts = explode('-', $version, 2);
        $numbers = explode('.', $parts[0]);

        while (count($numbers) > 2 && '0' === end($numbers)) {
            array_pop($numbers);
        }

        return implode('.', $numbers).(isset($parts[1]) && 'dev' !== $parts[1] ? '-'.$parts[1] : '');
    }

    private function loadPackages(string $path, bool $dev, bool $withPlatform, bool $allowMissingFiles): ArrayRepository
    {
        return $this->loadPackagesFromArray($this->decode($this->getFileContents($path, true, $allowMissingFiles), $path), $dev, $withPlatform);
    }

    /**
     * @return string[]
     */
    private function getDirectPackages(string $path): array
    {
        $data = $this->decode($this->getFileContents($path, false, true), $path);

        $packages = [];

        foreach (['require', 'require-dev'] as $key) {
            foreach (array_keys($data[$key] ?? []) as $name) {
                $packages[] = strtolower((string) $name);
            }
        }

        return $packages;
    }

    /**
     * @return mixed[]
     */
    private function decode(string $contents, string $path): array
    {
        $data = \json_decode($contents, true);

        if (!is_array($data)) {
            throw new \RuntimeException(sprintf('Could not parse %s as JSON: %s', $path, json_last_error_msg()));
        }

        return $data;
    }

    private function getFileContents(string $path, bool $lockFile, bool $allowMissingFiles): string
    {
        $originalPath = $path;

        if (empty($path)) {
            $path = self::COMPOSER.($lockFile ? self::EXTENSION_LOCK : self::EXTENSION_JSON);
        }

        $localPath = $path;

        if (!$lockFile) {
            $localPath = $this->getJsonPath($localPath);
        }

        if (filter_var($localPath, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) || file_exists($localPath)) {
            $contents = @file_get_contents($localPath);

            if (false === $contents) {
                throw new \RuntimeException(sprintf('Could not read %s', $localPath));
            }

            return $contents;
        }

        if (false === strpos($originalPath, self::GIT_SEPARATOR)) {
            $path .= self::GIT_SEPARATOR.self::CURRENT_DIRECTORY.self::COMPOSER.($lockFile ? self::EXTENSION_LOCK : self::EXTENSION_JSON);
        }

        if (!$lockFile) {
            $path = $this->getJsonPath($path);
        }

        $process = new ProcessExecutor();
        $output = '';

        if (0 !== $process->execute(sprintf('git show %s', ProcessExecutor::escape($path)), $output)) {
            if (!$allowMissingFiles) {
                throw new \RuntimeException(sprintf('Could not open file %s or find it in git as %s: %s', $originalPath, $path, trim($process->getErrorOutput())));
            }

            /* @infection-ignore-all False-positive */
            return '{}';
        }

        return $output;
    }

    private function getJsonPath(string $path): string
    {
        if (self::EXTENSION_LOCK === substr($path, -strlen(self::EXTENSION_LOCK))) {
            return substr($path, 0, -strlen(self::EXTENSION_LOCK)).self::EXTENSION_JSON;
        }

        return $path;
    }
}
