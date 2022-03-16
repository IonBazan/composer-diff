<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Tests\Url;

use IonBazan\ComposerDiff\Url\GithubGenerator;
use IonBazan\ComposerDiff\Url\UrlGenerator;

class GithubGeneratorTest extends GeneratorTest
{
    public function testDomainQuotingWillNotHandleInvalidDomain(): void
    {
        $package = $this->getPackageWithSource('acme/package', '3.12.1', 'git@githubacom:acme/package.git');
        $this->assertSame('git@githubacom:acme/package/releases/tag/3.12.1', $this->getGenerator()->getReleaseUrl($package));
    }

    public function releaseUrlProvider(): iterable
    {
        yield 'with .git' => [
            $this->getPackageWithSource('acme/package', '3.12.1', 'https://github.com/acme/package.git'),
            'https://github.com/acme/package/releases/tag/3.12.1',
        ];
        yield 'without .git' => [
            $this->getPackageWithSource('acme/package', '3.12.1', 'https://github.com/acme/package'),
            'https://github.com/acme/package/releases/tag/3.12.1',
        ];
        yield 'ssh with .git' => [
            $this->getPackageWithSource('acme/package', '3.12.1', 'git@github.com:acme/package.git'),
            'https://github.com/acme/package/releases/tag/3.12.1',
        ];
        yield 'ssh without .git' => [
            $this->getPackageWithSource('acme/package', '3.12.1', 'git@github.com:acme/package'),
            'https://github.com/acme/package/releases/tag/3.12.1',
        ];
        yield 'dev version' => [
            $this->getPackageWithSource('acme/package', 'dev-master', 'git@github.com:acme/package'),
            null,
        ];
    }

    public function projectUrlProvider(): iterable
    {
        yield 'with .git' => [
            $this->getPackageWithSource('acme/package', '3.12.1', 'https://github.com/acme/package.git'),
            'https://github.com/acme/package',
        ];
        yield 'without .git' => [
            $this->getPackageWithSource('acme/package', '3.12.1', 'https://github.com/acme/package'),
            'https://github.com/acme/package',
        ];
        yield 'ssh with .git' => [
            $this->getPackageWithSource('acme/package', '3.12.1', 'git@github.com:acme/package.git'),
            'https://github.com/acme/package',
        ];
        yield 'ssh without .git' => [
            $this->getPackageWithSource('acme/package', '3.12.1', 'git@github.com:acme/package'),
            'https://github.com/acme/package',
        ];
        yield 'dev version' => [
            $this->getPackageWithSource('acme/package', 'dev-master', 'git@github.com:acme/package'),
            'https://github.com/acme/package',
        ];
    }

    public function compareUrlProvider(): iterable
    {
        yield 'same maintainer' => [
            $this->getPackageWithSource('acme/package', '3.12.0', 'https://github.com/acme/package.git'),
            $this->getPackageWithSource('acme/package', '3.12.1', 'https://github.com/acme/package.git'),
            'https://github.com/acme/package/compare/3.12.0..3.12.1',
        ];
        yield 'without .git' => [
            $this->getPackageWithSource('acme/package', '3.12.0', 'https://github.com/acme/package'),
            $this->getPackageWithSource('acme/package', '3.12.1', 'https://github.com/acme/package'),
            'https://github.com/acme/package/compare/3.12.0..3.12.1',
        ];
        yield 'dev versions' => [
            $this->getPackageWithSource('acme/package', 'dev-master', 'https://github.com/acme/package.git', 'd46283075d76ed244f7825b378eeb1cee246af73'),
            $this->getPackageWithSource('acme/package', 'dev-master', 'https://github.com/acme/package.git', '9b860214d58c48b5cbe99bdb17914d0eb723c9cd'),
            'https://github.com/acme/package/compare/d462830..9b86021',
        ];
        yield 'invalid or short reference' => [
            $this->getPackageWithSource('acme/package', 'dev-master', 'https://github.com/acme/package.git', 'd462830'),
            $this->getPackageWithSource('acme/package', 'dev-master', 'https://github.com/acme/package.git', '1'),
            'https://github.com/acme/package/compare/d462830..1',
        ];
        yield 'compare with base fork' => [
            $this->getPackageWithSource('acme/package', '3.12.0', 'https://github.com/IonBazan/package.git'),
            $this->getPackageWithSource('acme/package', '3.12.1', 'https://github.com/acme/package.git'),
            'https://github.com/IonBazan/package/compare/3.12.0..acme:3.12.1',
        ];
        yield 'compare with head fork' => [
            $this->getPackageWithSource('acme/package', '3.12.0', 'https://github.com/acme/package.git'),
            $this->getPackageWithSource('acme/package', '3.12.1', 'https://github.com/IonBazan/package.git'),
            'https://github.com/acme/package/compare/3.12.0..IonBazan:3.12.1',
        ];
        yield 'compare with different repository provider' => [
            $this->getPackageWithSource('acme/package', '3.12.0', 'https://github.com/acme/package.git'),
            $this->getPackageWithSource('acme/package', '3.12.1', 'https://bitbucket.org/acme/package.git'),
            null,
        ];
    }

    protected function getGenerator(): UrlGenerator
    {
        return new GithubGenerator();
    }
}
