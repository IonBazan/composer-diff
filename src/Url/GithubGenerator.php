<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

class GithubGenerator extends GitGenerator
{
    /**
     * {@inheritdoc}
     */
    public function getCompareUrl(PackageInterface $initialPackage, PackageInterface $targetPackage)
    {
        if (!$this->supportsPackage($initialPackage) || !$this->supportsPackage($targetPackage)) {
            return null;
        }

        $baseUrl = $this->getRepositoryUrl($initialPackage);
        $baseMaintainer = $this->getUser($initialPackage);
        $targetMaintainer = $this->getUser($targetPackage);
        $targetVersion = ($baseMaintainer !== $targetMaintainer ? $targetMaintainer.':' : '').$this->getCompareRef($targetPackage);

        return sprintf('%s/compare/%s...%s', $baseUrl, $this->getCompareRef($initialPackage), $targetVersion);
    }

    /**
     * {@inheritdoc}
     */
    public function getReleaseUrl(PackageInterface $package)
    {
        if ($package->isDev()) {
            return null;
        }

        return sprintf('%s/releases/tag/%s', $this->getRepositoryUrl($package), $package->getPrettyVersion());
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectUrl(PackageInterface $package)
    {
        return $this->getRepositoryUrl($package);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDomain()
    {
        return 'github.com';
    }
}
