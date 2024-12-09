<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

class WordPressGenerator implements UrlGenerator
{
    /**
     * {@inheritdoc}
     */
    public function supportsPackage(PackageInterface $package)
    {
        return (bool) preg_match('#^wpackagist-(plugin|theme)/#', $package->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function getCompareUrl(PackageInterface $initialPackage, PackageInterface $targetPackage)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getReleaseUrl(PackageInterface $package)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectUrl(PackageInterface $package)
    {
        preg_match('#wpackagist-(plugin|theme)/(.+)#', $package->getName(), $matches);

        if (empty($matches)) {
            return null;
        }

        list (, $type, $slug) = $matches;

        return sprintf('https://wordpress.org/%ss/%s', $type, $slug);
    }
}
