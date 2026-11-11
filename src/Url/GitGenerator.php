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

    /**
     * {@inheritdoc}
     */
    public function getCompareUrl(PackageInterface $initialPackage, PackageInterface $targetPackage): ?string
    {
        if (!$this->supportsPackage($initialPackage) || !$this->supportsPackage($targetPackage)) {
            return null;
        }

        $baseUser = $this->getUser($initialPackage);
        $targetUser = $this->getUser($targetPackage);
        $template = $this->getCompareUrlTemplate($baseUser === $targetUser);

        if (null === $template) {
            return $this->getReleaseUrl($targetPackage); // Could not get a compare URL, using release URL instead
        }

        $baseRef = $this->getCompareRef($initialPackage);
        $targetRef = $this->getCompareRef($targetPackage);

        if ($baseRef === $targetRef) {
            // Same version, so only the source references can tell the packages apart
            $baseRef = $initialPackage->getSourceReference();
            $targetRef = $targetPackage->getSourceReference();
        }

        if (!$baseRef || !$targetRef || $baseRef === $targetRef) {
            return null;
        }

        return strtr($template, [
            '{base_url}' => $this->getRepositoryUrl($initialPackage),
            '{target_url}' => $this->getRepositoryUrl($targetPackage),
            '{base_user}' => $baseUser,
            '{target_user}' => $targetUser,
            '{base_repo}' => $this->getRepo($initialPackage),
            '{target_repo}' => $this->getRepo($targetPackage),
            '{base_ref}' => $baseRef,
            '{target_ref}' => $targetRef,
        ]);
    }

    /**
     * Gets the template for compare URLs, in which these placeholders get replaced: {base_url}, {target_url},
     * {base_user}, {target_user}, {base_repo}, {target_repo}, {base_ref} and {target_ref}.
     *
     * @param bool $sameUser whether both packages come from the same user (or organization), rather than from forks
     *
     * @return string|null null if there is no way to compare in that case, so the release URL is used instead
     */
    abstract protected function getCompareUrlTemplate(bool $sameUser): ?string;

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
