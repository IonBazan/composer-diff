<?php

namespace IonBazan\ComposerDiff\Tests\Url;

use IonBazan\ComposerDiff\Url\GithubGenerator;

class GithubGeneratorTest extends GeneratorTest
{
    public function testDomainQuotingWillNotHandleInvalidDomain()
    {
        $package = $this->getPackageWithSource('acme/package', '3.12.1', 'git@githubacom:acme/package.git');
        $this->assertSame('git@githubacom:acme/package/releases/tag/3.12.1', $this->getGenerator()->getReleaseUrl($package));
    }

    public static function releaseUrlProvider()
    {
        return array(
            'with .git' => array(
                'https://github.com/acme/package/releases/tag/3.12.1',
                'acme/package', '3.12.1', 'https://github.com/acme/package.git',
            ),
            'without .git' => array(
                'https://github.com/acme/package/releases/tag/3.12.1',
                'acme/package', '3.12.1', 'https://github.com/acme/package',
            ),
            'ssh with .git' => array(
                'https://github.com/acme/package/releases/tag/3.12.1',
                'acme/package', '3.12.1', 'git@github.com:acme/package.git',
            ),
            'ssh without .git' => array(
                'https://github.com/acme/package/releases/tag/3.12.1',
                'acme/package', '3.12.1', 'git@github.com:acme/package',
            ),
            'dev version' => array(
                null,
                'acme/package', 'dev-master', 'git@github.com:acme/package',
            ),
        );
    }

    public static function projectUrlProvider()
    {
        return array(
            'with .git' => array(
                'https://github.com/acme/package',
                'acme/package', '3.12.1', 'https://github.com/acme/package.git',
            ),
            'without .git' => array(
                'https://github.com/acme/package',
                'acme/package', '3.12.1', 'https://github.com/acme/package',
            ),
            'ssh with .git' => array(
                'https://github.com/acme/package',
                'acme/package', '3.12.1', 'git@github.com:acme/package.git',
            ),
            'ssh without .git' => array(
                'https://github.com/acme/package',
                'acme/package', '3.12.1', 'git@github.com:acme/package',
            ),
            'dev version' => array(
                'https://github.com/acme/package',
                'acme/package', 'dev-master', 'git@github.com:acme/package',
            ),
        );
    }

    public static function compareUrlProvider()
    {
        return array(
            'same maintainer' => array(
                array('name' => 'acme/package', 'version' => '3.12.0', 'source' => 'https://github.com/acme/package.git'),
                array('name' => 'acme/package', 'version' => '3.12.1', 'source' => 'https://github.com/acme/package.git'),
                'https://github.com/acme/package/compare/3.12.0..3.12.1',
            ),
            'without .git' => array(
                array('name' => 'acme/package', 'version' => '3.12.0', 'source' => 'https://github.com/acme/package'),
                array('name' => 'acme/package', 'version' => '3.12.1', 'source' => 'https://github.com/acme/package'),
                'https://github.com/acme/package/compare/3.12.0..3.12.1',
            ),
            'dev versions' => array(
                array('name' => 'acme/package', 'version' => 'dev-master', 'source' => 'https://github.com/acme/package.git', 'sourceReference' => 'd46283075d76ed244f7825b378eeb1cee246af73'),
                array('name' => 'acme/package', 'version' => 'dev-master', 'source' => 'https://github.com/acme/package.git', 'sourceReference' => '9b860214d58c48b5cbe99bdb17914d0eb723c9cd'),
                'https://github.com/acme/package/compare/d462830..9b86021',
            ),
            'invalid or short reference' => array(
                array('name' => 'acme/package', 'version' => 'dev-master', 'source' => 'https://github.com/acme/package.git', 'sourceReference' => 'd462830'),
                array('name' => 'acme/package', 'version' => 'dev-master', 'source' => 'https://github.com/acme/package.git', 'sourceReference' => '1'),
                'https://github.com/acme/package/compare/d462830..1',
            ),
            'compare with base fork' => array(
                array('name' => 'acme/package', 'version' => '3.12.0', 'source' => 'https://github.com/IonBazan/package.git'),
                array('name' => 'acme/package', 'version' => '3.12.1', 'source' => 'https://github.com/acme/package.git'),
                'https://github.com/IonBazan/package/compare/3.12.0..acme:3.12.1',
            ),
            'compare with head fork' => array(
                array('name' => 'acme/package', 'version' => '3.12.0', 'source' => 'https://github.com/acme/package.git'),
                array('name' => 'acme/package', 'version' => '3.12.1', 'source' => 'https://github.com/IonBazan/package.git'),
                'https://github.com/acme/package/compare/3.12.0..IonBazan:3.12.1',
            ),
            'compare with different repository provider' => array(
                array('name' => 'acme/package', 'version' => '3.12.0', 'source' => 'https://github.com/acme/package.git'),
                array('name' => 'acme/package', 'version' => '3.12.1', 'source' => 'https://bitbucket.org/acme/package.git'),
                null,
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getGenerator()
    {
        return new GithubGenerator();
    }
}
