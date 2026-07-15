<?php

namespace IonBazan\ComposerDiff\Tests\Url;

use IonBazan\ComposerDiff\Url\GithubGenerator;
use IonBazan\ComposerDiff\Url\GitlabGenerator;
use IonBazan\ComposerDiff\Url\DrupalGenerator;
use Composer\Package\CompletePackage;
use IonBazan\ComposerDiff\Tests\TestCase;
use IonBazan\ComposerDiff\Url\GeneratorContainer;

class GeneratorContainerTest extends TestCase
{
    public function testGetsProperGenerator(): void
    {
        $container = new GeneratorContainer(['gitlab2.org']);
        $githubGenerator = $container->get($this->getPackageWithSource('', '', 'https://github.com'));
        $this->assertInstanceOf(GithubGenerator::class, $githubGenerator);
        $gitlabGenerator = $container->get($this->getPackageWithSource('', '', 'https://gitlab.org'));
        $this->assertInstanceOf(GitlabGenerator::class, $gitlabGenerator);
        $gitlab2Generator = $container->get($this->getPackageWithSource('', '', 'https://gitlab2.org'));
        $this->assertInstanceOf(GitlabGenerator::class, $gitlab2Generator);
        $this->assertNotSame($gitlabGenerator, $gitlab2Generator);
        $this->assertNull($container->get($this->getPackageWithSource('', '', 'https://gitlab3.org')));
        $this->assertNull($container->get($this->getPackageWithSource('', '', null)));
        $drupalGenerator = $container->get($this->getPackageWithSource('', '', 'https://git.drupalcode.org'));
        $this->assertInstanceOf(DrupalGenerator::class, $drupalGenerator);
        $this->assertNotSame($gitlabGenerator, $drupalGenerator);
    }

    public function testItSupportsPackageSupportedByOneOfTheGenerators(): void
    {
        $generator = new GeneratorContainer([]);
        self::assertTrue($generator->supportsPackage($this->getPackageWithSource('acme/package', '3.12.0', 'https://bitbucket.org/acme/package.git')));
        self::assertFalse($generator->supportsPackage($this->getPackageWithSource('acme/package', '3.12.0', 'https://non-existent.org/acme/package.git')));
    }

    public function testItReturnsHomepageForProjectUrlWhenNoGeneratorSupportsPackage(): void
    {
        $generator = new GeneratorContainer();
        $package = new CompletePackage('acme/package', '3.12.0', '3.12.0');
        $package->setHomepage('https://acme.com');

        self::assertSame('https://acme.com', $generator->getProjectUrl($package));

        $package->setHomepage(null);
        self::assertNull($generator->getProjectUrl($package));
    }
}
