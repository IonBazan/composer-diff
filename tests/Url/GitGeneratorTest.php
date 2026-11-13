<?php

namespace IonBazan\ComposerDiff\Tests\Url;

use Composer\Package\PackageInterface;
use IonBazan\ComposerDiff\Tests\TestCase;
use IonBazan\ComposerDiff\Url\GitlabGenerator;

class GitGeneratorTest extends TestCase
{
    /**
     * @dataProvider userAndRepoProvider
     */
    public function testDeprecatedUserAndRepo(string $sourceUrl, string $user, string $repo): void
    {
        $generator = new class('gitlab.acme.org') extends GitlabGenerator {
            /**
             * @return string[]
             */
            public function getUserAndRepo(PackageInterface $package): array
            {
                return [$this->getUser($package), $this->getRepo($package)];
            }
        };

        $package = $this->getPackageWithSource('acme/package', '1.0.0', $sourceUrl);

        $this->assertSame([$user, $repo], $generator->getUserAndRepo($package));
    }

    /**
     * @return iterable<array<string>>
     */
    public function userAndRepoProvider(): iterable
    {
        return [
            'https' => ['https://gitlab.acme.org/acme/package.git', 'acme', 'package'],
            'ssh' => ['git@gitlab.acme.org:acme/package.git', 'acme', 'package'],
            'subgroup' => ['https://gitlab.acme.org/ac/me/package', 'ac/me', 'package'],
        ];
    }
}
