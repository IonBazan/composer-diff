<?php

namespace IonBazan\ComposerDiff\Tests\Url;

use IonBazan\ComposerDiff\Tests\TestCase;
use IonBazan\ComposerDiff\Url\GeneratorContainer;

class GeneratorContainerTest extends TestCase
{
    public function testGetsProperGenerator()
    {
        $container = new GeneratorContainer(array('gitlab2.org'));
        $githubGenerator = $container->get($this->getPackageWithSource('', '', 'https://github.com'));
        $this->assertInstanceOf('IonBazan\ComposerDiff\Url\GithubGenerator', $githubGenerator);
        $gitlabGenerator = $container->get($this->getPackageWithSource('', '', 'https://gitlab.org'));
        $this->assertInstanceOf('IonBazan\ComposerDiff\Url\GitlabGenerator', $gitlabGenerator);
        $gitlab2Generator = $container->get($this->getPackageWithSource('', '', 'https://gitlab2.org'));
        $this->assertInstanceOf('IonBazan\ComposerDiff\Url\GitlabGenerator', $gitlab2Generator);
        $this->assertNotSame($gitlabGenerator, $gitlab2Generator);
        $this->assertNull($container->get($this->getPackageWithSource('', '', 'https://gitlab3.org')));
    }
}
