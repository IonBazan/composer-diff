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

        $baseUrl = $this->getRepositoryUrl($targetPackage);
        $baseUser = $this->getUser($initialPackage);
        $targetUser = $this->getUser($targetPackage);

        if ($baseUser === $targetUser) {
            return sprintf(
                '%s/branches/compare/%s%%0D%s',
                $baseUrl,
                $targetRef,
                $baseRef
            );
        }

        return sprintf(
            '%s/branches/compare/%s/%s:%s%%0D%s/%s:%s',
            $baseUrl,
            $targetUser,
            $this->getRepo($targetPackage),
            $targetRef,
            $baseUser,
            $this->getRepo($initialPackage),
            $baseRef
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
