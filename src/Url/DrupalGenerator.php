<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

class DrupalGenerator extends GitlabGenerator
{
    const DRUPAL_CORE = 'drupal/core';

    /**
     * {@inheritdoc}
     */
    public function supportsPackage(PackageInterface $package): bool
    {
        return self::DRUPAL_CORE === $package->getName() || parent::supportsPackage($package);
    }

    protected function getCompareRef(PackageInterface $package): string
    {
        if (!$package->isDev()) {
            return $package->getDistReference();
        }

        return parent::getCompareRef($package);
    }

    /**
     * {@inheritdoc}
     */
    public function getReleaseUrl(PackageInterface $package): ?string
    {
        // Not sure we can support dev releases right now. Can we distinguish major version dev releases from regular branches?
        if ($package->isDev()) {
            return null;
        }

        return sprintf('%s/releases/%s', $this->getProjectUrl($package), $this->getVersionReference($package));
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectUrl(PackageInterface $package): ?string
    {
        return sprintf('https://www.drupal.org/project/%s', $this->getDrupalProjectName($package));
    }

    /**
     * {@inheritdoc}
     */
    protected function getDomain(): string
    {
        return 'git.drupalcode.org';
    }

    private function getVersionReference(PackageInterface $package): ?string
    {
        if ($package->getDistReference()) {
            return $package->getDistReference();
        }

        return $package->getSourceReference();
    }

    private function getDrupalProjectName(PackageInterface $package): ?string
    {
        if (self::DRUPAL_CORE === $package->getName()) {
            return 'drupal';
        }

        return preg_replace('/^drupal\//', '', $package->getName());
    }
}
