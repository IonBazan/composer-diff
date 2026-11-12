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
    public function getCompareUrl(PackageInterface $initialPackage, PackageInterface $targetPackage): ?string
    {
        if (!$this->supportsPackage($initialPackage) || !$this->supportsPackage($targetPackage)) {
            return null;
        }

        $baseUrl = $this->getRepositoryUrl($initialPackage);

        if ($baseUrl !== $this->getRepositoryUrl($targetPackage)) {
            return $this->getReleaseUrl($targetPackage); // Could not get a compare URL, using release URL instead
        }

        return sprintf('%s/compare/%s...%s', $baseUrl, $this->getCompareRefAgainst($initialPackage, $targetPackage), $this->getCompareRefAgainst($targetPackage, $initialPackage));
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
