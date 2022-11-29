<?php

namespace IonBazan\ComposerDiff\Tests\Url;

use Composer\Package\PackageInterface;
use IonBazan\ComposerDiff\Tests\TestCase;
use IonBazan\ComposerDiff\Url\UrlGenerator;

/**
 * @phpstan-type PackageInformation array{name: string, version: string, source:string, sourceReference?: string}
 */
abstract class GeneratorTest extends TestCase
{
    /**
     * @param string $expectedUrl
     *
     * @dataProvider compareUrlProvider
     */
    public function testCompareUrl(array $initial, array $target, $expectedUrl)
    {
        $initialPackage = $this->getPackageWithSource($initial['name'], $initial['version'], $initial['source'], isset($initial['sourceReference']) ? $initial['sourceReference'] : null);
        $targetPackage = $this->getPackageWithSource($target['name'], $target['version'], $target['source'], isset($target['sourceReference']) ? $target['sourceReference'] : null);
        $this->assertSame($expectedUrl, $this->getGenerator()->getCompareUrl($initialPackage, $targetPackage));
    }

    /**
     * @param string|null $expectedUrl
     * @param string $name
     * @param string $version
     * @param string $sourceUrl
     * @param string|null $sourceReference
     *
     * @dataProvider releaseUrlProvider
     */
    public function testReleaseUrl($expectedUrl, $name, $version, $sourceUrl, $sourceReference = null)
    {
        $package = $this->getPackageWithSource($name, $version, $sourceUrl, $sourceReference);
        $this->assertSame($expectedUrl, $this->getGenerator()->getReleaseUrl($package));
    }

    /**
     * @param string|null $expectedUrl
     * @param string $name
     * @param string $version
     * @param string $sourceUrl
     * @param string|null $sourceReference
     *
     * @dataProvider projectUrlProvider
     */
    public function testProjectUrl($expectedUrl, $name, $version, $sourceUrl, $sourceReference = null)
    {
        $package = $this->getPackageWithSource($name, $version, $sourceUrl, $sourceReference);
        $this->assertSame($expectedUrl, $this->getGenerator()->getProjectUrl($package));
    }

    /**
     * @return array<array{PackageInformation, PackageInformation, string}>
     */
    abstract public static function compareUrlProvider();

    abstract public static function releaseUrlProvider();

    abstract public static function projectUrlProvider();

    /**
     * @return UrlGenerator
     */
    abstract protected function getGenerator();
}
