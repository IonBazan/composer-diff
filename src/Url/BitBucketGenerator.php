<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

class BitBucketGenerator extends GitGenerator
{
    /**
     * {@inheritdoc}
     */
    protected function getDomain(): string
    {
        return 'bitbucket.org';
    }

    /**
     * {@inheritdoc}
     */
    public function getCompareUrl(PackageInterface $initialPackage, PackageInterface $targetPackage): ?string
    {
        if (!$this->supportsPackage($initialPackage) || !$this->supportsPackage($targetPackage)) {
            return null;
        }

        $baseUrl = $this->getRepositoryUrl($targetPackage);
        $basePath = $this->getRepositoryPath($initialPackage);
        $targetPath = $this->getRepositoryPath($targetPackage);

        if ($basePath === $targetPath) {
            return sprintf(
                '%s/branches/compare/%s%%0D%s',
                $baseUrl,
                $this->getCompareRefAgainst($targetPackage, $initialPackage),
                $this->getCompareRefAgainst($initialPackage, $targetPackage)
            );
        }

        return sprintf(
            '%s/branches/compare/%s:%s%%0D%s:%s',
            $baseUrl,
            $targetPath,
            $this->getCompareRefAgainst($targetPackage, $initialPackage),
            $basePath,
            $this->getCompareRefAgainst($initialPackage, $targetPackage)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getReleaseUrl(PackageInterface $package): ?string
    {
        return sprintf('%s/src/%s', $this->getRepositoryUrl($package), $package->isDev() ? $package->getSourceReference() : $package->getPrettyVersion());
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectUrl(PackageInterface $package): ?string
    {
        return $this->getRepositoryUrl($package);
    }
}
