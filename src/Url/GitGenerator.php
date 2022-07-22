<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

abstract class GitGenerator implements UrlGenerator
{
    const REFERENCE_LENGTH = 40;
    const SHORT_REFERENCE_LENGTH = 7;

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

        if (self::REFERENCE_LENGTH === \strlen($reference)) {
            return \substr($reference, 0, self::SHORT_REFERENCE_LENGTH);
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
            "/^git@(?:git\.)?({$this->getQuotedDomain()}):(.+)\/([^\/]+)(\.git)?$/",
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
