<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

class WordPressGenerator implements UrlGenerator
{
    /**
     * Determines if the generator supports the given package.
     *
     * @return bool
     */
    public function supportsPackage(PackageInterface $package)
    {
        return 0 === strpos($package->getName(), 'wpackagist-plugin/') || 0 === strpos($package->getName(), 'wpackagist-theme/');
    }

    /**
     * Generates a compare URL for two versions of the same package.
     *
     * @return string|null
     */
    public function getCompareUrl(PackageInterface $initialPackage, PackageInterface $targetPackage)
    {
        return null;
    }

    /**
     * Generates URL for viewing a release or commit of a package.
     *
     * @return string|null
     */
    public function getReleaseUrl(PackageInterface $package)
    {
        return null;
    }

    /**
     * Generates URL for viewing the project page of a package (usually repository root).
     *
     * @return string|null
     */
    public function getProjectUrl(PackageInterface $package)
    {
        $type = $this->getPackageType($package);

        if (null === $type) {
            return null;
        }

        return sprintf('https://wordpress.org/%ss/%s', $type, $this->getPackageSlug($package));
    }

    /**
     * @return string|null
     */
    protected function getPackageType(PackageInterface $package)
    {
        [$type] = explode('/', $package->getName(), 2);

        return 0 === strpos($type, 'wpackagist-') ? substr($type, 11) : null;
    }

    /**
     * @return string
     */
    protected function getPackageSlug(PackageInterface $package)
    {
        [, $slug] = explode('/', $package->getName(), 2);

        return $slug;
    }
}
