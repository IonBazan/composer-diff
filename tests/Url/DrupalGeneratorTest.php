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
              $this->getPackageWithSource('drupal/webform', '6.0.0', 'https://git.drupalcode.org/project/webform.git'),
              'https://www.drupal.org/project/webform',
              $this->getPackageWithSource('drupal/webform', '6.0.1', 'https://git.drupalcode.org/project/webform.git'),
              'https://www.drupal.org/project/webform',
                'https://gitlab.acme.org/acme/package/compare/6.0.0...6.0.1',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getGenerator()
    {
        return new DrupalGenerator();
    }
}
