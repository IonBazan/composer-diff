<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

class GithubGenerator extends GitGenerator
{
    /**
     * {@inheritdoc}
     */
    public function getCompareUrl(PackageInterface $initialPackage, PackageInterface $targetPackage): ?string
    {
        if (!$this->supportsPackage($initialPackage) || !$this->supportsPackage($targetPackage)) {
            return null;
        }

        $refs = $this->getCompareRefs($initialPackage, $targetPackage);

        if (null === $refs) {
            return null;
        }

        $baseUrl = $this->getRepositoryUrl($initialPackage);
        $baseMaintainer = $this->getUser($initialPackage);
        $targetMaintainer = $this->getUser($targetPackage);
        $targetVersion = ($baseMaintainer !== $targetMaintainer ? $targetMaintainer.':' : '').$refs[1];

        return sprintf('%s/compare/%s...%s', $baseUrl, $refs[0], $targetVersion);
    }

    /**
     * {@inheritdoc}
     */
    public function getReleaseUrl(PackageInterface $package): ?string
    {
        if ($package->isDev()) {
            return null;
        }

        return sprintf('%s/releases/tag/%s', $this->getRepositoryUrl($package), $package->getPrettyVersion());
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectUrl(PackageInterface $package): ?string
    {
        return $this->getRepositoryUrl($package);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDomain(): string
    {
        return 'github.com';
    }
}
