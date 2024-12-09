<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\CompletePackage;
use Composer\Package\PackageInterface;

class GeneratorContainer implements UrlGenerator
{
    /**
     * @var UrlGenerator[]
     */
    protected $generators = array();

    /**
     * @param string[] $gitlabDomains
     */
    public function __construct(array $gitlabDomains = array())
    {
        $generators = array(
            new DrupalGenerator(),
            new GithubGenerator(),
            new BitBucketGenerator(),
            new GitlabGenerator(),
        );

        foreach ($gitlabDomains as $domain) {
            $generators[] = new GitlabGenerator($domain);
        }

        $this->generators = $generators;
    }

    /**
     * @return UrlGenerator|null
     */
    public function get(PackageInterface $package)
    {
        foreach ($this->generators as $generator) {
            if ($generator->supportsPackage($package)) {
                return $generator;
            }
        }

        return null;
    }

    public function supportsPackage(PackageInterface $package)
    {
        return null !== $this->get($package);
    }

    public function getCompareUrl(PackageInterface $initialPackage, PackageInterface $targetPackage)
    {
        if (!$generator = $this->get($targetPackage)) {
            return null;
        }

        return $generator->getCompareUrl($initialPackage, $targetPackage);
    }

    public function getReleaseUrl(PackageInterface $package)
    {
        if (!$generator = $this->get($package)) {
            return null;
        }

        return $generator->getReleaseUrl($package);
    }

    public function getProjectUrl(PackageInterface $package)
    {
        if ($generator = $this->get($package)) {
            return $generator->getProjectUrl($package);
        }

        if ($package instanceof CompletePackage) {
            return $package->getHomepage();
        }

        return null;
    }
}
