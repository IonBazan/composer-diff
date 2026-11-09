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
    public function supportsPackage(PackageInterface $package): bool
    {
        return false !== strpos((string) $package->getSourceUrl(), $this->getDomain());
    }

    protected function getCompareRef(PackageInterface $package): string
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
     * Finds the references to compare between two packages.
     *
     * When both packages resolve to the same reference (e.g. a tag which has been moved to another commit),
     * their immutable source references are used instead. If there is nothing to tell them apart, no
     * comparison can be made, as it would be empty.
     *
     * @return array{string, string}|null base and target references, or null if they cannot be compared
     */
    protected function getCompareRefs(PackageInterface $initialPackage, PackageInterface $targetPackage): ?array
    {
        $baseRef = $this->getCompareRef($initialPackage);
        $targetRef = $this->getCompareRef($targetPackage);

        if ($baseRef !== $targetRef) {
            return [$baseRef, $targetRef];
        }

        $baseSource = $initialPackage->getSourceReference();
        $targetSource = $targetPackage->getSourceReference();

        if (!$baseSource || !$targetSource || $baseSource === $targetSource) {
            return null;
        }

        return [$baseSource, $targetSource];
    }

    protected function getUser(PackageInterface $package): string
    {
        return preg_replace(
            "/^https:\/\/{$this->getQuotedDomain()}\/(.+)\/([^\/]+)$/",
            '$1',
            $this->getRepositoryUrl($package)
        );
    }

    protected function getRepo(PackageInterface $package): string
    {
        return preg_replace(
            "/^https:\/\/{$this->getQuotedDomain()}\/(.+)\/([^\/]+)$/",
            '$2',
            $this->getRepositoryUrl($package)
        );
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

    private function getQuotedDomain(): string
    {
        return preg_quote($this->getDomain(), '/');
    }

    abstract protected function getDomain(): string;
}
