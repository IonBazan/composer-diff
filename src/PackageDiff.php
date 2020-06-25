<?php

namespace IonBazan\ComposerDiff;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\CompletePackage;
use Composer\Package\Loader\ArrayLoader;
use Composer\Repository\ArrayRepository;

class PackageDiff
{
    const LOCKFILE = 'composer.lock';

    /**
     * @param string $from
     * @param string $to
     * @param bool   $dev
     * @param bool   $withPlatform
     *
     * @return OperationInterface[]
     */
    public function getPackageDiff($from, $to, $dev, $withPlatform)
    {
        $oldPackages = $this->loadPackages($from, $dev, $withPlatform);
        $targetPackages = $this->loadPackages($to, $dev, $withPlatform);

        $operations = array();

        foreach ($targetPackages->getPackages() as $newPackage) {
            if ($oldPackage = $oldPackages->findPackage($newPackage->getName(), '*')) {
                if ($oldPackage->getUniqueName() !== $newPackage->getUniqueName()) {
                    $operations[] = new UpdateOperation($oldPackage, $newPackage);
                }

                continue;
            }

            $operations[] = new InstallOperation($newPackage);
        }

        foreach ($oldPackages->getPackages() as $oldPackage) {
            if (!$targetPackages->findPackage($oldPackage->getName(), '*')) {
                $operations[] = new UninstallOperation($oldPackage);
            }
        }

        return $operations;
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
            return file_get_contents($path);
        }

        if (false === strpos($originalPath, ':')) {
            $path .= ':'.self::LOCKFILE;
        }

        $output = array();
        @exec(sprintf('git show %s 2>&1', escapeshellarg($path)), $output, $exit);

        if (0 !== $exit) {
            throw new \RuntimeException(sprintf('Could not open file %s or find it in git as %s', $originalPath, $path));
        }

        return implode("\n", $output);
    }
}
