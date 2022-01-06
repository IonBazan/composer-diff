<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

interface UrlGenerator
{
    /**
     * @return bool
     */
    public function supportsPackage(PackageInterface $package);

    /**
     * @return string|null
     */
    public function getCompareUrl(PackageInterface $initialPackage, PackageInterface $targetPackage);

    /**
     * @return string|null
     */
    public function getReleaseUrl(PackageInterface $package);

    /**
     * @return string|null
     */
    public function getProjectUrl(PackageInterface $package);
}
