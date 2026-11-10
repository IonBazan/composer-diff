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

        $baseRef = $this->getCompareRef($initialPackage);
        $targetRef = $this->getCompareRef($targetPackage);

        if ($baseRef === $targetRef) {
            // Same version, so only the source references can tell the packages apart
            $baseRef = $initialPackage->getSourceReference();
            $targetRef = $targetPackage->getSourceReference();
        }

        if (!$baseRef || !$targetRef || $baseRef === $targetRef) {
            return null;
        }

        $baseUrl = $this->getRepositoryUrl($initialPackage);
        $baseMaintainer = $this->getUser($initialPackage);
        $targetMaintainer = $this->getUser($targetPackage);
        $targetVersion = ($baseMaintainer !== $targetMaintainer ? $targetMaintainer.':' : '').$targetRef;

        return sprintf('%s/compare/%s...%s', $baseUrl, $baseRef, $targetVersion);
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
