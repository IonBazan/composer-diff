<?php

namespace IonBazan\ComposerDiff\Tests\Url;

use IonBazan\ComposerDiff\Url\WordPressGenerator;

class WordPressGeneratorTest extends GeneratorTest
{
    public function testItSupportsOnlyWpackagistPackages()
    {
        $generator = $this->getGenerator();

        $this->assertFalse($generator->supportsPackage($this->getPackage('acme/package', '3.12.1')));
        $this->assertTrue($generator->supportsPackage($this->getPackage('wpackagist-plugin/my-plugin', '3.12.1')));
        $this->assertTrue($generator->supportsPackage($this->getPackage('wpackagist-theme/my-theme', '3.12.1')));
        $this->assertFalse($generator->supportsPackage($this->getPackage('acme-wpackagist-theme/my-theme', '3.12.1')));
    }

    public function releaseUrlProvider()
    {
        return array(
            'plugin' => array(
                $this->getPackageWithSource('wpackagist-plugin/jetpack', '13.1', 'https://plugins.svn.wordpress.org/jetpack/', '13.1'),
                null,
            ),
            'theme' => array(
                $this->getPackageWithSource('wpackagist-theme/twentytwenty', '1.7', 'https://themes.svn.wordpress.org/twentytwenty/', '1.7'),
                null,
            ),
        );
    }

    public function projectUrlProvider()
    {
        return array(
            'plugin' => array(
                $this->getPackageWithSource('wpackagist-plugin/jetpack', '13.1', 'https://plugins.svn.wordpress.org/jetpack/', '13.1'),
                'https://wordpress.org/plugins/jetpack',
            ),
            'theme' => array(
                $this->getPackageWithSource('wpackagist-theme/twentytwenty', '1.7', 'https://themes.svn.wordpress.org/twentytwenty/', '1.7'),
                'https://wordpress.org/themes/twentytwenty',
            ),
        );
    }

    public function compareUrlProvider()
    {
        return array(
            'plugin' => array(
                $this->getPackageWithSource('wpackagist-plugin/jetpack', '13.1', 'https://plugins.svn.wordpress.org/jetpack/', '13.1'),
                $this->getPackageWithSource('wpackagist-plugin/jetpack', '13.2', 'https://plugins.svn.wordpress.org/jetpack/', '13.2'),
                null,
            ),
            'theme' => array(
                $this->getPackageWithSource('wpackagist-theme/twentytwenty', '1.7', 'https://themes.svn.wordpress.org/twentytwenty/', '1.7'),
                $this->getPackageWithSource('wpackagist-theme/twentytwenty', '1.8', 'https://themes.svn.wordpress.org/twentytwenty/', '1.8'),
                null,
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getGenerator()
    {
        return new WordPressGenerator();
    }
}