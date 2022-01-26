<?php declare(strict_types=1);

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

interface UrlGenerator
{
    public function supportsPackage(PackageInterface $package): bool;

    public function getCompareUrl(PackageInterface $initialPackage, PackageInterface $targetPackage): ?string;

    public function getReleaseUrl(PackageInterface $package): ?string;

    public function getProjectUrl(PackageInterface $package): ?string;
}
