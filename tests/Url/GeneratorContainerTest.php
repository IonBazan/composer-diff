<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Tests\Url;

use IonBazan\ComposerDiff\Tests\TestCase;
use IonBazan\ComposerDiff\Url\BitBucketGenerator;
use IonBazan\ComposerDiff\Url\GeneratorContainer;
use IonBazan\ComposerDiff\Url\GithubGenerator;
use IonBazan\ComposerDiff\Url\GitlabGenerator;

class GeneratorContainerTest extends TestCase
{
    public function testItSupportsPackageSupportedByOneOfTheGenerators(): void
    {
        $generator = new GeneratorContainer([]);
        self::assertTrue($generator->supportsPackage($this->getPackageWithSource('acme/package', '3.12.0', 'https://bitbucket.org/acme/package.git')));
        self::assertFalse($generator->supportsPackage($this->getPackageWithSource('acme/package', '3.12.0', 'https://non-existent.org/acme/package.git')));
    }

    public function testItRetrievesGeneratorBasedOnDomain(): void
    {
        $generator = new GeneratorContainer([]);
        self::assertInstanceOf(BitBucketGenerator::class, $generator->get($this->getPackageWithSource('acme/package', '3.12.0', 'https://bitbucket.org/acme/package.git')));
        self::assertInstanceOf(GithubGenerator::class, $generator->get($this->getPackageWithSource('acme/package', '3.12.0', 'https://github.com/acme/package.git')));
        self::assertInstanceOf(GitlabGenerator::class, $generator->get($this->getPackageWithSource('acme/package', '3.12.0', 'https://gitlab.org/acme/package.git')));
        self::assertNull($generator->get($this->getPackageWithSource('acme/package', '3.12.0', 'https://non-existent.org/acme/package.git')));
    }
}
