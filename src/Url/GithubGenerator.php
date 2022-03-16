<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

class GithubGenerator extends GitGenerator
{
    public function getCompareUrl(PackageInterface $initialPackage, PackageInterface $targetPackage): ?string
    {
        if (!$this->supportsPackage($initialPackage) || !$this->supportsPackage($targetPackage)) {
            return null;
        }

        $baseUrl = $this->getRepositoryUrl($initialPackage);
        $baseMaintainer = $this->getUser($initialPackage);
        $targetMaintainer = $this->getUser($targetPackage);
        $targetVersion = ($baseMaintainer !== $targetMaintainer ? $targetMaintainer.':' : '').$this->getCompareRef($targetPackage);

        return sprintf('%s/compare/%s..%s', $baseUrl, $this->getCompareRef($initialPackage), $targetVersion);
    }

    public function getReleaseUrl(PackageInterface $package): ?string
    {
        if ($package->isDev()) {
            return null;
        }

        return sprintf('%s/releases/tag/%s', $this->getRepositoryUrl($package), $package->getPrettyVersion());
    }

    public function getProjectUrl(PackageInterface $package): string
    {
        return $this->getRepositoryUrl($package);
    }

    protected function getDomain(): string
    {
        return 'github.com';
    }
}
