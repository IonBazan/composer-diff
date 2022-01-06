<?php

namespace IonBazan\ComposerDiff\Tests\Url;

use Composer\Package\PackageInterface;
use IonBazan\ComposerDiff\Tests\TestCase;
use IonBazan\ComposerDiff\Url\UrlGenerator;

abstract class GeneratorTest extends TestCase
{
    /**
     * @param string $expectedUrl
     *
     * @dataProvider compareUrlProvider
     */
    public function testCompareUrl(PackageInterface $initial, PackageInterface $target, $expectedUrl)
    {
        $this->assertSame($expectedUrl, $this->getGenerator()->getCompareUrl($initial, $target));
    }

    /**
     * @param string|null $expectedUrl
     *
     * @dataProvider releaseUrlProvider
     */
    public function testReleaseUrl(PackageInterface $package, $expectedUrl)
    {
        $this->assertSame($expectedUrl, $this->getGenerator()->getReleaseUrl($package));
    }

    /**
     * @param string|null $expectedUrl
     *
     * @dataProvider projectUrlProvider
     */
    public function testProjectUrl(PackageInterface $package, $expectedUrl)
    {
        $this->assertSame($expectedUrl, $this->getGenerator()->getProjectUrl($package));
    }

    abstract public function compareUrlProvider();

    abstract public function releaseUrlProvider();

    abstract public function projectUrlProvider();

    /**
     * @return UrlGenerator
     */
    abstract protected function getGenerator();
}
