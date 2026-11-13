<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;
use IonBazan\ComposerDiff\Diff\VersionComparator;

abstract class GitGenerator implements UrlGenerator
{
    const REFERENCE_LENGTH = 40;
    const SHORT_REFERENCE_LENGTH = 7;

    /**
     * Package getter methods that identify a package's revision on this host, keyed by label.
     */
    const REFERENCE_FIELDS = VersionComparator::REFERENCE_FIELDS;

    /**
     * {@inheritdoc}
     */
    public function supportsPackage(PackageInterface $package): bool
    {
        return false !== strpos((string) $package->getSourceUrl(), $this->getDomain());
    }

    protected function getCompareRef(PackageInterface $package): string
    {
        if (!$package->isDev()) {
            return $package->getPrettyVersion();
        }

        return $this->shortenReference($package->getSourceReference());
    }

    /**
     * Uses the source reference when both packages have the same version, e.g. when a tag was moved.
     */
    protected function getCompareRefAgainst(PackageInterface $package, PackageInterface $other): string
    {
        $reference = $this->getCompareRef($package);

        if ($reference === $this->getCompareRef($other) && $package->getSourceReference()) {
            return $this->shortenReference($package->getSourceReference());
        }

        return $reference;
    }

    /**
     * @deprecated since 2.5.1, use getRepositoryPath() instead
     */
    protected function getUser(PackageInterface $package): string
    {
        return preg_replace(
            "/^https:\/\/{$this->getQuotedDomain()}\/(.+)\/([^\/]+)$/",
            '$1',
            $this->getRepositoryUrl($package)
        );
    }

    /**
     * @deprecated since 2.5.1, use getRepositoryPath() instead
     */
    protected function getRepo(PackageInterface $package): string
    {
        return preg_replace(
            "/^https:\/\/{$this->getQuotedDomain()}\/(.+)\/([^\/]+)$/",
            '$2',
            $this->getRepositoryUrl($package)
        );
    }

    /**
     * Gets the repository path after the domain, e.g. "owner/repo" or "group/subgroup/repo".
     */
    protected function getRepositoryPath(PackageInterface $package): string
    {
        return preg_replace("/^https:\/\/{$this->getQuotedDomain()}\//", '', $this->getRepositoryUrl($package));
    }

    protected function getRepositoryUrl(PackageInterface $package): string
    {
        $httpsUrl = preg_replace(
            "/^git@(?:git\.)?({$this->getQuotedDomain()}):(.+)\/([^\/]+)(\.git)?$/",
            'https://$1/$2/$3',
            $package->getSourceUrl()
        );

        return preg_replace('#^(.+)\.git$#', '$1', $httpsUrl);
    }

    private function shortenReference(string $reference): string
    {
        if (self::REFERENCE_LENGTH === \strlen($reference)) {
            return \substr($reference, 0, self::SHORT_REFERENCE_LENGTH);
        }

        return $reference;
    }

    private function getQuotedDomain(): string
    {
        return preg_quote($this->getDomain(), '/');
    }

    abstract protected function getDomain(): string;
}
