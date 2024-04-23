<?php

namespace IonBazan\ComposerDiff\Tests\Url;

use IonBazan\ComposerDiff\Url\DrupalGenerator;

class DrupalGeneratorTest extends GeneratorTest
{
    public function releaseUrlProvider()
    {
        return array(
            'contrib-legacy-version' => array(
                $this->getPackageWithSource('drupal/token', '8.x-1.0', 'https://git.drupalcode.org/project/token.git'),
                'https://www.drupal.org/project/token/releases/8.x-1.0',
            ),
            'contrib-semver-version' => array(
                $this->getPackageWithSource('drupal/webform', '6.0.0', 'https://git.drupalcode.org/project/webform.git'),
                'https://www.drupal.org/project/webform/releases/6.0.0',
            ),
            'core' => array(
                $this->getPackageWithSource('drupal/core', '9.0.0', 'https://github.com/drupal/core.git'),
                'https://www.drupal.org/project/drupal/releases/9.0.0',
            ),
            'core-dev' => array(
                $this->getPackageWithSource('drupal/core', 'dev-9.0.x', 'https://github.com/drupal/core.git'),
                null,
            ),
            'core-dev-alternate' => array(
                $this->getPackageWithSource('drupal/core', '9.0.x-dev', 'https://github.com/drupal/core.git'),
                null,
            ),
            'contrib-dev' => array(
                $this->getPackageWithSource('drupal/webform', 'dev-9.0.x', 'https://github.com/drupal/core.git'),
                null,
            ),
            'contrib-dev-alternate' => array(
                $this->getPackageWithSource('drupal/webform', 'dev-9.0.x', 'https://github.com/drupal/core.git'),
                null,
            ),
        );
    }

    public function projectUrlProvider()
    {
        return array(
            'contrib-legacy-version' => array(
                $this->getPackageWithSource('drupal/token', '8.x-1.0', 'https://git.drupalcode.org/project/token.git'),
                'https://www.drupal.org/project/token',
            ),
            'contrib-semver-version' => array(
                $this->getPackageWithSource('drupal/webform', '6.0.0', 'https://git.drupalcode.org/project/webform.git'),
                'https://www.drupal.org/project/webform',
            ),
            'core' => array(
                $this->getPackageWithSource('drupal/core', '9.0.0', 'https://github.com/drupal/core.git'),
                'https://www.drupal.org/project/drupal',
            ),
        );
    }

    public function compareUrlProvider()
    {
        return array(
            'semver' => array(
              $this->getPackageWithSourceAndDist('drupal/webform', '6.0.0', '6.0.0', 'https://git.drupalcode.org/project/webform.git'),
              $this->getPackageWithSourceAndDist('drupal/webform', '6.0.1', '6.0.1', 'https://git.drupalcode.org/project/webform.git'),
                'https://git.drupalcode.org/project/webform/compare/6.0.0...6.0.1',
            ),
        );
    }

    /**
     * @param string      $name
     * @param string      $version
     * @param string|null $sourceUrl
     * @param string|null $sourceReference
     *
     * @return mixed
     */
    protected function getPackageWithSourceAndDist($name, $version, $dist_version, $sourceUrl, $sourceReference = null)
    {
        $package = $this->getPackage($name, $version, $sourceReference);
        $package->method('getSourceUrl')->willReturn($sourceUrl);
        $package->method('getDistReference')->willReturn($dist_version);
        $package->method('getSourceReference')->willReturn($sourceReference);
        $package->method('isDev')->willReturn(0 === strpos($version, 'dev-') || '-dev' === substr($version, -4));

        return $package;
    }

    /**
     * {@inheritdoc}
     */
    protected function getGenerator()
    {
        return new DrupalGenerator();
    }
}
