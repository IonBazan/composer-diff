<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Tests\Url;

use IonBazan\ComposerDiff\Tests\TestCase;
use IonBazan\ComposerDiff\Url\GeneratorContainer;
use IonBazan\ComposerDiff\Url\GithubGenerator;
use IonBazan\ComposerDiff\Url\GitlabGenerator;

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
    }
}
