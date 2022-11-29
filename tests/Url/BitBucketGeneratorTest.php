<?php

namespace IonBazan\ComposerDiff\Tests\Url;

use IonBazan\ComposerDiff\Url\BitBucketGenerator;

class BitBucketGeneratorTest extends GeneratorTest
{
    public static function releaseUrlProvider()
    {
        return array(
            'with .git' => array(
                'https://bitbucket.org/acme/package/src/3.12.1',
                'acme/package',
                '3.12.1',
                'https://bitbucket.org/acme/package.git',
            ),
            'without .git' => array(
                'https://bitbucket.org/acme/package/src/3.12.1',
                'acme/package',
                '3.12.1',
                'https://bitbucket.org/acme/package',
            ),
            'ssh with .git' => array(
                'https://bitbucket.org/acme/package/src/3.12.1',
                'acme/package',
                '3.12.1',
                'git@bitbucket.org:acme/package.git',
            ),
            'ssh without .git' => array(
                'https://bitbucket.org/acme/package/src/3.12.1',
                'acme/package',
                '3.12.1',
                'git@bitbucket.org:acme/package',
            ),
            'dev version' => array(
                'https://bitbucket.org/acme/package/src/d46283075d76ed244f7825b378eeb1cee246af73',
                'acme/package',
                'dev-master',
                'git@bitbucket.org:acme/package',
                'd46283075d76ed244f7825b378eeb1cee246af73',
            ),
        );
    }

    public static function projectUrlProvider()
    {
        return array(
            'with .git' => array(
                'https://bitbucket.org/acme/package',
                'acme/package',
                '3.12.1',
                'https://bitbucket.org/acme/package.git',
            ),
            'without .git' => array(
                'https://bitbucket.org/acme/package',
                'acme/package',
                '3.12.1',
                'https://bitbucket.org/acme/package',
            ),
            'ssh with .git' => array(
                'https://bitbucket.org/acme/package',
                'acme/package',
                '3.12.1',
                'git@bitbucket.org:acme/package.git',
            ),
            'ssh without .git' => array(
                'https://bitbucket.org/acme/package',
                'acme/package',
                '3.12.1',
                'git@bitbucket.org:acme/package',
            ),
            'dev version' => array(
                'https://bitbucket.org/acme/package',
                'acme/package',
                'dev-master',
                'git@bitbucket.org:acme/package',
                'd46283075d76ed244f7825b378eeb1cee246af73',
            ),
        );
    }

    public static function compareUrlProvider()
    {
        return array(
            'same maintainer' => array(
                array('name' => 'acme/package', 'version' => '3.12.0', 'source' => 'https://bitbucket.org/acme/package.git'),
                array('name' => 'acme/package', 'version' => '3.12.1', 'source' => 'https://bitbucket.org/acme/package.git'),
                'https://bitbucket.org/acme/package/branches/compare/3.12.0%0D3.12.1',
            ),
            'without .git' => array(
                array('name' => 'acme/package', 'version' => '3.12.0', 'source' => 'https://bitbucket.org/acme/package'),
                array('name' => 'acme/package', 'version' => '3.12.1', 'source' => 'https://bitbucket.org/acme/package'),
                'https://bitbucket.org/acme/package/branches/compare/3.12.0%0D3.12.1',
            ),
            'dev versions' => array(
                array('name' => 'acme/package', 'version' => 'dev-master', 'source' => 'https://bitbucket.org/acme/package.git', 'sourceReference' => 'd46283075d76ed244f7825b378eeb1cee246af73'),
                array('name' => 'acme/package', 'version' => 'dev-master', 'source' => 'https://bitbucket.org/acme/package.git', 'sourceReference' => '9b860214d58c48b5cbe99bdb17914d0eb723c9cd'),
                'https://bitbucket.org/acme/package/branches/compare/d462830%0D9b86021',
            ),
            'invalid or short reference' => array(
                array('name' => 'acme/package', 'version' => 'dev-master', 'source' => 'https://bitbucket.org/acme/package.git', 'sourceReference' => 'd462830'),
                array('name' => 'acme/package', 'version' => 'dev-master', 'source' => 'https://bitbucket.org/acme/package.git', 'sourceReference' => '1'),
                'https://bitbucket.org/acme/package/branches/compare/d462830%0D1',
            ),
            'compare with base fork' => array(
                array('name' => 'acme/package', 'version' => '3.12.0', 'source' => 'https://bitbucket.org/IonBazan/package.git'),
                array('name' => 'acme/package', 'version' => '3.12.1', 'source' => 'https://bitbucket.org/acme/package.git'),
                'https://bitbucket.org/acme/package/branches/compare/IonBazan/package:3.12.0%0Dacme/package:3.12.1',
            ),
            'compare with head fork' => array(
                array('name' => 'acme/package', 'version' => '3.12.0', 'source' => 'https://bitbucket.org/acme/package.git'),
                array('name' => 'acme/package', 'version' => '3.12.1', 'source' => 'https://bitbucket.org/IonBazan/package.git'),
                'https://bitbucket.org/IonBazan/package/branches/compare/acme/package:3.12.0%0DIonBazan/package:3.12.1',
            ),
            'compare with different repository provider' => array(
                array('name' => 'acme/package', 'version' => '3.12.0', 'source' => 'https://bitbucket.org/acme/package.git'),
                array('name' => 'acme/package', 'version' => '3.12.1', 'source' => 'https://gitlab.org/acme/package.git'),
                null,
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getGenerator()
    {
        return new BitBucketGenerator();
    }
}
