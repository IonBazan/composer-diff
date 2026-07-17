<?php

namespace IonBazan\ComposerDiff;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\AliasPackage;
use Composer\Package\CompletePackage;
use Composer\Package\Loader\ArrayLoader;
use Composer\Repository\ArrayRepository;
use Composer\Repository\RepositoryInterface;
use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Url\GeneratorContainer;
use IonBazan\ComposerDiff\Url\UrlGenerator;

class PackageDiff
{
    const COMPOSER = 'composer';
    const EXTENSION_LOCK = '.lock';
    const EXTENSION_JSON = '.json';
    const GIT_SEPARATOR = ':';

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
            $direct = in_array($package->getName(), $directPackages, true);

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
            $matchingPackages = $oldPackages->findPackages($newPackage->getName());

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

            if (!$targetPackages->findPackage($oldPackage->getName(), '*')) {
                $operations[] = new UninstallOperation($oldPackage);
            }
        }

        return $operations;
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
        $platformKey = 'platform'.($dev ? '-dev' : '');

        if (isset($composerLock[$packagesKey])) {
            foreach ($composerLock[$packagesKey] as $packageInfo) {
                $packages[] = $loader->load($packageInfo);
            }
        }

        if ($withPlatform && isset($composerLock[$platformKey])) {
            foreach ($composerLock[$platformKey] as $name => $version) {
                $packages[] = new CompletePackage($name, $version, $version);
            }
        }

        return new ArrayRepository($packages);
    }

    private function loadPackages(string $path, bool $dev, bool $withPlatform, bool $allowMissingFiles): ArrayRepository
    {
        $data = \json_decode($this->getFileContents($path, true, $allowMissingFiles), true);

        return $this->loadPackagesFromArray($data, $dev, $withPlatform);
    }

    /**
     * @return string[]
     */
    private function getDirectPackages(string $path): array
    {
        $data = \json_decode($this->getFileContents($path, false), true);

        $packages = [];

        foreach (['require', 'require-dev'] as $key) {
            if (isset($data[$key])) {
                $packages = array_merge($packages, array_keys($data[$key]));
            }
        }

        return $packages; // @phpstan-ignore return.type
    }

    private function getFileContents(string $path, bool $lockFile = true, bool $allowMissingFiles = false): string
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
            // @phpstan-ignore return.type
            return file_get_contents($localPath);
        }

        if (false === strpos($originalPath, self::GIT_SEPARATOR)) {
            $path .= self::GIT_SEPARATOR.self::COMPOSER.($lockFile ? self::EXTENSION_LOCK : self::EXTENSION_JSON);
        }

        if (!$lockFile) {
            $path = $this->getJsonPath($path);
        }

        $output = [];
        @exec(sprintf('git show %s 2>&1', escapeshellarg($path)), $output, $exit);
        $outputString = implode("\n", $output);

        if (0 !== $exit) {
            if ($lockFile && !$allowMissingFiles) {
                throw new \RuntimeException(sprintf('Could not open file %s or find it in git as %s: %s', $originalPath, $path, $outputString));
            }

            /* @infection-ignore-all False-positive */
            return '{}';
        }

        return $outputString;
    }

    private function getJsonPath(string $path): string
    {
        if (self::EXTENSION_LOCK === substr($path, -strlen(self::EXTENSION_LOCK))) {
            return substr($path, 0, -strlen(self::EXTENSION_LOCK)).self::EXTENSION_JSON;
        }

        return $path;
    }
}
