<?php

namespace IonBazan\ComposerDiff;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\AliasPackage;
use Composer\Package\CompletePackage;
use Composer\Package\Loader\ArrayLoader;
use Composer\Repository\ArrayRepository;
use Composer\Repository\RepositoryInterface;
use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;

class PackageDiff
{
    const LOCKFILE = 'composer.lock';

    /**
     * @return DiffEntries
     */
    public function getDiff(RepositoryInterface $oldPackages, RepositoryInterface $targetPackages)
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

        return new DiffEntries(array_map(function (OperationInterface $operation) {
            return new DiffEntry($operation);
        }, $operations));
    }

    /**
     * @param string $from
     * @param string $to
     * @param bool   $dev
     * @param bool   $withPlatform
     *
     * @return DiffEntries
     */
    public function getPackageDiff($from, $to, $dev, $withPlatform)
    {
        return $this->getDiff(
            $this->loadPackages($from, $dev, $withPlatform),
            $this->loadPackages($to, $dev, $withPlatform)
        );
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
        $loader = new ArrayLoader();

        $packages = array();

        foreach ($data['packages'.($dev ? '-dev' : '')] as $packageInfo) {
            $packages[] = $loader->load($packageInfo);
        }

        if ($withPlatform) {
            foreach ($data['platform'.($dev ? '-dev' : '')] as $name => $version) {
                $packages[] = new CompletePackage($name, $version, $version);
            }
        }

        return new ArrayRepository($packages);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getFileContents($path)
    {
        $originalPath = $path;

        if (empty($path)) {
            $path = self::LOCKFILE;
        }

        if (filter_var($path, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) || file_exists($path)) {
            // @phpstan-ignore return.type
            return file_get_contents($path);
        }

        if (false === strpos($originalPath, ':')) {
            $path .= ':'.self::LOCKFILE;
        }

        $output = array();
        @exec(sprintf('git show %s 2>&1', escapeshellarg($path)), $output, $exit);
        $outputString = implode("\n", $output);

        if (0 !== $exit) {
            throw new \RuntimeException(sprintf('Could not open file %s or find it in git as %s: %s', $originalPath, $path, $outputString));
        }

        return $outputString;
    }
}
