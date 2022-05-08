<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

class GeneratorContainer implements UrlGenerator
{
    /**
     * @var UrlGenerator[]
     */
    protected $generators = [];

    /**
     * @param string[] $gitlabDomains
     */
    public function __construct(array $gitlabDomains)
    {
        $generators = [
            new GithubGenerator(),
            new BitBucketGenerator(),
            new GitlabGenerator(),
        ];

        foreach ($gitlabDomains as $domain) {
            $generators[] = new GitlabGenerator($domain);
        }

        $this->generators = $generators;
    }

    public function get(PackageInterface $package): ?UrlGenerator
    {
        foreach ($this->generators as $generator) {
            if ($generator->supportsPackage($package)) {
                return $generator;
            }
        }

        return null;
    }

    public function supportsPackage(PackageInterface $package): bool
    {
        return null !== $this->get($package);
    }

    public function getCompareUrl(PackageInterface $initialPackage, PackageInterface $targetPackage): ?string
    {
        if (!$generator = $this->get($targetPackage)) {
            return null;
        }

        return $generator->getCompareUrl($initialPackage, $targetPackage);
    }

    public function getReleaseUrl(PackageInterface $package): ?string
    {
        if (!$generator = $this->get($package)) {
            return null;
        }

        return $generator->getReleaseUrl($package);
    }

    public function getProjectUrl(PackageInterface $package): ?string
    {
        if (!$generator = $this->get($package)) {
            return null;
        }

        return $generator->getProjectUrl($package);
    }
}
