<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

class GeneratorContainer
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
}
