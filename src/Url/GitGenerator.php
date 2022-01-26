<?php declare(strict_types=1);

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

abstract class GitGenerator implements UrlGenerator
{
    public function supportsPackage(PackageInterface $package): bool
    {
        return false !== strpos($package->getSourceUrl(), $this->getDomain());
    }

    protected function getCompareRef(PackageInterface $package): string
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
            "/^git@({$this->getQuotedDomain()}):(.+)\/([^\/]+)(\.git)?$/",
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
