<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

interface UrlGenerator
{
    /**
     * Determines if the generator supports the given package.
     *
     * @return bool
     */
    public function supportsPackage(PackageInterface $package);

    /**
     * Generates a compare URL for two versions of the same package.
     *
     * @return string|null
     */
    public function getCompareUrl(PackageInterface $initialPackage, PackageInterface $targetPackage);

    /**
     * Generates URL for viewing a release or commit of a package.
     *
     * @return string|null
     */
    public function getReleaseUrl(PackageInterface $package);

    /**
     * Generates URL for viewing the project page of a package (usually repository root).
     *
     * @return string|null
     */
    public function getProjectUrl(PackageInterface $package);
}
