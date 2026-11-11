<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

class GitlabGenerator extends GitGenerator
{
    /**
     * @var string
     */
    private $domain;

    public function __construct(string $domain = 'gitlab.com')
    {
        $this->domain = $domain;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCompareUrlTemplate(bool $sameUser): ?string
    {
        return $sameUser ? '{base_url}/compare/{base_ref}...{target_ref}' : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getReleaseUrl(PackageInterface $package): ?string
    {
        if ($package->isDev()) {
            return null;
        }

        return sprintf('%s/tags/%s', $this->getRepositoryUrl($package), $package->getPrettyVersion());
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectUrl(PackageInterface $package): ?string
    {
        return $this->getRepositoryUrl($package);
    }
}
