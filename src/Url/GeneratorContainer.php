<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

class GeneratorContainer
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
        $generators = array(
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
}
