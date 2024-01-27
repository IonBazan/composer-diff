<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Package\PackageInterface;

class DrupalGenerator extends GitlabGenerator
{
    /**
     * {@inheritdoc}
     */
    public function supportsPackage(PackageInterface $package)
    {
        return 'drupal/core' === $package->getName() || parent::supportsPackage($package);
    }

    /**
     * {@inheritdoc}
     */
    public function getReleaseUrl(PackageInterface $package)
    {
        $name = $this->getDrupalProjectName($package);
        $version = $package->getPrettyVersion();

        // Not sure we can support dev releases right now. Can we distinguish major version dev releases from regular branches?
        if ($package->isDev()) {
            return null;
        }

        // Always move dev-branchname to branchname-dev
        // if ($package->isDev() && substr($version, 0, 4) === 'dev-' && substr($version, -4) !== '-dev') {
        //    $version = substr($version, 4) . '-dev';
        // }

        return sprintf('https://www.drupal.org/project/%s/releases/%s', $name, $version);
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectUrl(PackageInterface $package)
    {
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
