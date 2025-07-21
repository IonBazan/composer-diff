<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

class BitBucketGenerator extends GitGenerator
{
    /**
     * {@inheritdoc}
     */
    protected function getDomain()
    {
        return 'bitbucket.org';
    }

    /**
     * {@inheritdoc}
     */
    public function getCompareUrl(PackageInterface $initialPackage, PackageInterface $targetPackage)
    {
        if (!$this->supportsPackage($initialPackage) || !$this->supportsPackage($targetPackage)) {
            return null;
        }

        $baseUrl = $this->getRepositoryUrl($targetPackage);
        $baseUser = $this->getUser($initialPackage);
        $targetUser = $this->getUser($targetPackage);

        if ($baseUser === $targetUser) {
            return sprintf(
                '%s/branches/compare/%s%%0D%s',
                $baseUrl,
                $this->getCompareRef($targetPackage),
                $this->getCompareRef($initialPackage)
            );
        }

        return sprintf(
            '%s/branches/compare/%s/%s:%s%%0D%s/%s:%s',
            $baseUrl,
            $targetUser,
            $this->getRepo($targetPackage),
            $this->getCompareRef($targetPackage),
            $baseUser,
            $this->getRepo($initialPackage),
            $this->getCompareRef($initialPackage)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getReleaseUrl(PackageInterface $package)
    {
        return sprintf('%s/src/%s', $this->getRepositoryUrl($package), $package->isDev() ? $package->getSourceReference() : $package->getPrettyVersion());
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectUrl(PackageInterface $package)
    {
        return $this->getRepositoryUrl($package);
    }
}
