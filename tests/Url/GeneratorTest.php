<?php declare(strict_types=1);

namespace IonBazan\ComposerDiff\Tests\Url;

use Composer\Package\PackageInterface;
use IonBazan\ComposerDiff\Tests\TestCase;
use IonBazan\ComposerDiff\Url\UrlGenerator;

abstract class GeneratorTest extends TestCase
{
    /**
     * @dataProvider compareUrlProvider
     */
    public function testCompareUrl(PackageInterface $initial, PackageInterface $target, ?string $expectedUrl): void
    {
        $this->assertSame($expectedUrl, $this->getGenerator()->getCompareUrl($initial, $target));
    }

    /**
     * @dataProvider releaseUrlProvider
     */
    public function testReleaseUrl(PackageInterface $package, ?string $expectedUrl): void
    {
        $this->assertSame($expectedUrl, $this->getGenerator()->getReleaseUrl($package));
    }

    /**
     * @dataProvider projectUrlProvider
     */
    public function testProjectUrl(PackageInterface $package, ?string $expectedUrl): void
    {
        $this->assertSame($expectedUrl, $this->getGenerator()->getProjectUrl($package));
    }

    abstract public function compareUrlProvider();

    abstract public function releaseUrlProvider();

    abstract public function projectUrlProvider();

    abstract protected function getGenerator(): UrlGenerator;
}
