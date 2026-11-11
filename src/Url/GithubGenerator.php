<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

class GithubGenerator extends GitGenerator
{
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
    protected function getCompareUrlTemplate(bool $sameUser): ?string
    {
        return $sameUser ? '{base_url}/compare/{base_ref}...{target_ref}' : '{base_url}/compare/{base_ref}...{target_user}:{target_ref}';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDomain(): string
    {
        return 'github.com';
    }
}
