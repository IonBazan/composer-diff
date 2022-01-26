<?php declare(strict_types=1);

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
    public const LOCKFILE = 'composer.lock';

    public function getDiff(RepositoryInterface $oldPackages, RepositoryInterface $targetPackages): DiffEntries
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

        return new DiffEntries(array_map(function (OperationInterface $operation) {
            return new DiffEntry($operation);
        }, $operations));
    }

    public function getPackageDiff(string $from, string $to, bool $dev, bool $withPlatform): DiffEntries
    {
        return $this->getDiff(
            $this->loadPackages($from, $dev, $withPlatform),
            $this->loadPackages($to, $dev, $withPlatform)
        );
    }

    private function loadPackages(string $path, bool $dev, bool $withPlatform): ArrayRepository
    {
        $data = \json_decode($this->getFileContents($path), true);
        $loader = new ArrayLoader();

        $packages = [];

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

    private function getFileContents(string $path): string
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

        $output = [];
        @exec(sprintf('git show %s 2>&1', escapeshellarg($path)), $output, $exit);

        if (0 !== $exit) {
            throw new \RuntimeException(sprintf('Could not open file %s or find it in git as %s', $originalPath, $path));
        }

        return implode("\n", $output);
    }
}
