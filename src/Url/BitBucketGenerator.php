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

    /**
     * {@inheritdoc}
     */
    protected function getCompareUrlTemplate(bool $sameUser): ?string
    {
        if ($sameUser) {
            return '{target_url}/branches/compare/{target_ref}%0D{base_ref}';
        }

        return '{target_url}/branches/compare/{target_user}/{target_repo}:{target_ref}%0D{base_user}/{base_repo}:{base_ref}';
    }
}
