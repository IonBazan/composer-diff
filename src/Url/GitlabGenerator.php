<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

class GitlabGenerator extends GitGenerator
{
    /**
     * @var string
     */
    private $domain;

    /**
     * @param string $domain
     */
    public function __construct($domain = 'gitlab.org')
    {
        $this->domain = $domain;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDomain()
    {
        return $this->domain;
    }

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

        if ($baseMaintainer !== $targetMaintainer) {
            return $this->getReleaseUrl($targetPackage); // Could not get a compare URL, using release URL instead
        }

        return sprintf('%s/compare/%s...%s', $baseUrl, $this->getCompareRef($initialPackage), $this->getCompareRef($targetPackage));
    }

    /**
     * {@inheritdoc}
     */
    public function getReleaseUrl(PackageInterface $package)
    {
        if ($package->isDev()) {
            return null;
        }

        return sprintf('%s/tags/%s', $this->getRepositoryUrl($package), $package->getPrettyVersion());
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectUrl(PackageInterface $package)
    {
        return $this->getRepositoryUrl($package);
    }
}
