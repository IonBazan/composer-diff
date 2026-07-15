<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

interface UrlGenerator
{
    /**
     * Determines if the generator supports the given package.
     */
    public function supportsPackage(PackageInterface $package): bool;

    /**
     * Generates a compare URL for two versions of the same package.
     */
    public function getCompareUrl(PackageInterface $initialPackage, PackageInterface $targetPackage): ?string;

    /**
     * Generates URL for viewing a release or commit of a package.
     */
    public function getReleaseUrl(PackageInterface $package): ?string;

    /**
     * Generates URL for viewing the project page of a package (usually repository root).
     */
    public function getProjectUrl(PackageInterface $package): ?string;
}
