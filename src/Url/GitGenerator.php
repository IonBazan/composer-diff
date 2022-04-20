<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

abstract class GitGenerator implements UrlGenerator
{
    /**
     * {@inheritdoc}
     */
    public function supportsPackage(PackageInterface $package)
    {
        return false !== strpos((string) $package->getSourceUrl(), $this->getDomain());
    }

    /**
     * @return string
     */
    protected function getCompareRef(PackageInterface $package)
    {
        if (!$package->isDev()) {
            return $package->getPrettyVersion();
        }

        $reference = $package->getSourceReference();

        if (40 === \strlen($reference)) {
            return \substr($reference, 0, 7);
        }

        return $reference;
    }

    /**
     * @return string
     */
    protected function getUser(PackageInterface $package)
    {
        return preg_replace(
            "/^https:\/\/{$this->getQuotedDomain()}\/(.+)\/([^\/]+)$/",
            '$1',
            $this->getRepositoryUrl($package)
        );
    }

    /**
     * @return string
     */
    protected function getRepo(PackageInterface $package)
    {
        return preg_replace(
            "/^https:\/\/{$this->getQuotedDomain()}\/(.+)\/([^\/]+)$/",
            '$2',
            $this->getRepositoryUrl($package)
        );
    }

    /**
     * @return string
     */
    protected function getRepositoryUrl(PackageInterface $package)
    {
        $httpsUrl = preg_replace(
            "/^git@({$this->getQuotedDomain()}):(.+)\/([^\/]+)(\.git)?$/",
            'https://$1/$2/$3',
            $package->getSourceUrl()
        );

        return preg_replace('#^(.+)\.git$#', '$1', $httpsUrl);
    }

    /**
     * @return string
     */
    private function getQuotedDomain()
    {
        return preg_quote($this->getDomain(), '/');
    }

    /**
     * @return string
     */
    abstract protected function getDomain();
}
