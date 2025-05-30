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

    /**
     * @return void
     */
    public function setUrlGenerator(UrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param string[] $directPackages
     * @param bool     $onlyDirect
     *
     * @return DiffEntries
     */
    public function getDiff(RepositoryInterface $oldPackages, RepositoryInterface $targetPackages, array $directPackages = array(), $onlyDirect = false)
    {
        $entries = array();

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
    public function getOperations(RepositoryInterface $oldPackages, RepositoryInterface $targetPackages)
    {
        $operations = array();

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

    /**
     * @param string $from
     * @param string $to
     * @param bool   $dev
     * @param bool   $withPlatform
     * @param bool   $onlyDirect
     *
     * @return DiffEntries
     */
    public function getPackageDiff($from, $to, $dev, $withPlatform, $onlyDirect = false)
    {
        return $this->getDiff(
            $this->loadPackages($from, $dev, $withPlatform),
            $this->loadPackages($to, $dev, $withPlatform),
            array_merge($this->getDirectPackages($from), $this->getDirectPackages($to)),
            $onlyDirect
        );
    }

    /**
     * @param mixed[] $composerLock
     * @param bool    $dev
     * @param bool    $withPlatform
     *
     * @return ArrayRepository
     */
    public function loadPackagesFromArray(array $composerLock, $dev, $withPlatform)
    {
        $loader = new ArrayLoader();
        $packages = array();
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

    /**
     * @param string $path
     * @param bool   $dev
     * @param bool   $withPlatform
     *
     * @return ArrayRepository
     */
    private function loadPackages($path, $dev, $withPlatform)
    {
        $data = \json_decode($this->getFileContents($path), true);

        return $this->loadPackagesFromArray($data, $dev, $withPlatform);
    }

    /**
     * @param string $path
     *
     * @return string[]
     */
    private function getDirectPackages($path)
    {
        $data = \json_decode($this->getFileContents($path, false), true);

        $packages = array();

        foreach (array('require', 'require-dev') as $key) {
            if (isset($data[$key])) {
                $packages = array_merge($packages, array_keys($data[$key]));
            }
        }

        return $packages; // @phpstan-ignore return.type
    }

    /**
     * @param string $path
     * @param bool   $lockFile
     *
     * @return string
     */
    private function getFileContents($path, $lockFile = true)
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

        $output = array();
        @exec(sprintf('git show %s 2>&1', escapeshellarg($path)), $output, $exit);
        $outputString = implode("\n", $output);

        if (0 !== $exit) {
            if ($lockFile) {
                throw new \RuntimeException(sprintf('Could not open file %s or find it in git as %s: %s', $originalPath, $path, $outputString));
            }

            return '{}'; // Do not throw exception for composer.json as it might not exist and that's fine
        }

        return $outputString;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getJsonPath($path)
    {
        if (self::EXTENSION_LOCK === substr($path, -strlen(self::EXTENSION_LOCK))) {
            return substr($path, 0, -strlen(self::EXTENSION_LOCK)).self::EXTENSION_JSON;
        }

        return $path;
    }
}
