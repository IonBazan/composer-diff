<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\CompletePackageInterface;
use Composer\Package\PackageInterface;

class DrupalGenerator extends GitlabGenerator
{
    /**
     * {@inheritdoc}
     */
    public function supportsPackage(PackageInterface $package)
    {
        return 'drupal/core' === $package->getName() || in_array($package->getType(), array('drupal-module', 'drupal-theme')) || parent::supportsPackage($package);
    }

    /**
     * @return string
     */
    protected function getCompareRef(PackageInterface $package)
    {
        if (!$package->isDev()) {
            return $package->getDistReference();
        }

        $reference = $package->getSourceReference();

        if (40 === \strlen($reference)) {
            return \substr($reference, 0, 7);
        }

        return $reference;
    }

    /**
     * {@inheritdoc}
     */
    public function getReleaseUrl(PackageInterface $package)
    {
        // Not sure we can support dev releases right now. Can we distinguish major version dev releases from regular branches?
        if ($package->isDev()) {
            return null;
        }

        if ($package->getDistReference()) {
            $version = $package->getDistReference();
        }
        elseif ($package->getSourceReference()) {
            $version = $package->getSourceReference();
        }
        else {
            return null;
        }

        return sprintf('%s/releases/%s', $this->getProjectUrl($package), $version);
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectUrl(PackageInterface $package)
    {
        if ($package instanceof CompletePackageInterface) {
            return $package->getHomepage();
        }

        $name = $this->getDrupalProjectName($package);

        return sprintf('https://www.drupal.org/project/%s', $name);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDomain()
    {
        return 'git.drupalcode.org';
    }

    protected function getDrupalProjectName(PackageInterface $package)
    {
        list(, $name) = explode('/', $package->getName(), 2);

        // Special handling for drupal/core only.
        if ('core' === $name) {
            $name = 'drupal';
        }

        return $name;
    }
}
