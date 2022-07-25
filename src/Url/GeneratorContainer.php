<?php

namespace IonBazan\ComposerDiff\Url;

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
    public function __construct(array $gitlabDomains)
    {
        $this->generators = array(
            new GithubGenerator(),
            new BitBucketGenerator(),
            new GitlabGenerator(),
            new GitlabGenerator('git.drupalcode.org'),
        );

        foreach ($gitlabDomains as $domain) {
            $this->generators[] = new GitlabGenerator($domain);
        }
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
        if (!$generator = $this->get($package)) {
            return null;
        }

        return $generator->getProjectUrl($package);
    }
}
