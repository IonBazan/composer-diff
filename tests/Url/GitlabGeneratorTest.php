<?php

namespace IonBazan\ComposerDiff\Tests\Url;

use IonBazan\ComposerDiff\Url\GitlabGenerator;

class GitlabGeneratorTest extends GeneratorTest
{
    public static function releaseUrlProvider()
    {
        return array(
            'with .git' => array(
                'https://gitlab.acme.org/acme/package/tags/3.12.1',
                'acme/package', '3.12.1', 'https://gitlab.acme.org/acme/package.git',
            ),
            'without .git' => array(
                'https://gitlab.acme.org/acme/package/tags/3.12.1',
                'acme/package', '3.12.1', 'https://gitlab.acme.org/acme/package'
            ),
            'ssh with .git' => array(
                'https://gitlab.acme.org/acme/package/tags/3.12.1',
                'acme/package', '3.12.1', 'git@gitlab.acme.org:acme/package.git'
            ),
            'ssh without .git' => array(
                'https://gitlab.acme.org/acme/package/tags/3.12.1',
                'acme/package', '3.12.1', 'git@gitlab.acme.org:acme/package'
            ),
            'dev version' => array(
                null,
                'acme/package', 'dev-master', 'git@gitlab.acme.org:ac/me/package'
            ),
            'https in subgroup' => array(
                'https://gitlab.acme.org/ac/me/package/tags/3.12.1',
                'ac/me/package', '3.12.1', 'https://gitlab.acme.org/ac/me/package.git'
            ),
            'ssh in subgroup' => array(
                'https://gitlab.acme.org/ac/me/package/tags/3.12.1',
                'ac/me/package', '3.12.1', 'git@gitlab.acme.org:ac/me/package.git'
            ),
        );
    }

    public static function projectUrlProvider()
    {
        return array(
            'with .git' => array(
                'https://gitlab.acme.org/acme/package',
                'acme/package', '3.12.1', 'https://gitlab.acme.org/acme/package.git'
            ),
            'without .git' => array(
                'https://gitlab.acme.org/acme/package',
                'acme/package', '3.12.1', 'https://gitlab.acme.org/acme/package'
            ),
            'ssh with .git' => array(
                'https://gitlab.acme.org/acme/package',
                'acme/package', '3.12.1', 'git@gitlab.acme.org:acme/package.git'
            ),
            'ssh without .git' => array(
                'https://gitlab.acme.org/acme/package',
                'acme/package', '3.12.1', 'git@gitlab.acme.org:acme/package'
            ),
            'dev version' => array(
                'https://gitlab.acme.org/ac/me/package',
                'acme/package', 'dev-master', 'git@gitlab.acme.org:ac/me/package'
            ),
            'https in subgroup' => array(
                'https://gitlab.acme.org/ac/me/package',
                'ac/me/package', '3.12.1', 'https://gitlab.acme.org/ac/me/package.git'
            ),
            'ssh in subgroup' => array(
                'https://gitlab.acme.org/ac/me/package',
                'ac/me/package', '3.12.1', 'git@gitlab.acme.org:ac/me/package.git'
            ),
        );
    }

    public static function compareUrlProvider()
    {
        return array(
            'same maintainer' => array(
                array('name' => 'acme/package', 'version' => '3.12.0', 'source' => 'https://gitlab.acme.org/acme/package.git'),
                array('name' => 'acme/package', 'version' => '3.12.1', 'source' => 'https://gitlab.acme.org/acme/package.git'),
                'https://gitlab.acme.org/acme/package/compare/3.12.0...3.12.1',
            ),
            'without .git' => array(
                array('name' => 'acme/package', 'version' => '3.12.0', 'source' => 'https://gitlab.acme.org/acme/package'),
                array('name' => 'acme/package', 'version' => '3.12.1', 'source' => 'https://gitlab.acme.org/acme/package'),
                'https://gitlab.acme.org/acme/package/compare/3.12.0...3.12.1',
            ),
            'dev versions' => array(
                array('name' => 'acme/package', 'version' => 'dev-master', 'source' => 'https://gitlab.acme.org/acme/package.git', 'sourceReference' => 'd46283075d76ed244f7825b378eeb1cee246af73'),
                array('name' => 'acme/package', 'version' => 'dev-master', 'source' => 'https://gitlab.acme.org/acme/package.git', 'sourceReference' => '9b860214d58c48b5cbe99bdb17914d0eb723c9cd'),
                'https://gitlab.acme.org/acme/package/compare/d462830...9b86021',
            ),
            'invalid or short reference' => array(
                array('name' => 'acme/package', 'version' => 'dev-master', 'source' => 'https://gitlab.acme.org/acme/package.git', 'sourceReference' => 'd462830'),
                array('name' => 'acme/package', 'version' => 'dev-master', 'source' => 'https://gitlab.acme.org/acme/package.git', 'sourceReference' => '1'),
                'https://gitlab.acme.org/acme/package/compare/d462830...1',
            ),
            'compare with base fork' => array(
                array('name' => 'acme/package', 'version' => '3.12.0', 'source' => 'https://gitlab.acme.org/IonBazan/package.git'),
                array('name' => 'acme/package', 'version' => '3.12.1', 'source' => 'https://gitlab.acme.org/acme/package.git'),
                'https://gitlab.acme.org/acme/package/tags/3.12.1',
            ),
            'compare with head fork' => array(
                array('name' => 'acme/package', 'version' => '3.12.0', 'source' => 'https://gitlab.acme.org/acme/package.git'),
                array('name' => 'acme/package', 'version' => '3.12.1', 'source' => 'https://gitlab.acme.org/IonBazan/package.git'),
                'https://gitlab.acme.org/IonBazan/package/tags/3.12.1',
            ),
            'compare with different repository provider' => array(
                array('name' => 'acme/package', 'version' => '3.12.0', 'source' => 'https://gitlab.acme.org/acme/package.git'),
                array('name' => 'acme/package', 'version' => '3.12.1', 'source' => 'https://gitlab.org/acme/package.git'),
                null,
            ),
            'compare from https in subgroup' => array(
                array('name' => 'acme/package', 'version' => '3.12.0', 'source' => 'https://gitlab.acme.org/ac/me/package'),
                array('name' => 'acme/package', 'version' => '3.12.1', 'source' => 'https://gitlab.acme.org/ac/me/package'),
                'https://gitlab.acme.org/ac/me/package/compare/3.12.0...3.12.1',
            ),
            'compare from ssh in subgroup' => array(
                array('name' => 'acme/package', 'version' => '3.12.0', 'source' => 'git@gitlab.acme.org:ac/me/package.git'),
                array('name' => 'acme/package', 'version' => '3.12.1', 'source' => 'git@gitlab.acme.org:ac/me/package.git'),
                'https://gitlab.acme.org/ac/me/package/compare/3.12.0...3.12.1',
            ),
            'compare with base fork from subgroups' => array(
                array('name' => 'acme/package', 'version' => '3.12.0', 'source' => 'https://gitlab.acme.org/Ion/Bazan/package.git'),
                array('name' => 'acme/package', 'version' => '3.12.1', 'source' => 'https://gitlab.acme.org/ac/me/package.git'),
                'https://gitlab.acme.org/ac/me/package/tags/3.12.1',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getGenerator()
    {
        return new GitlabGenerator('gitlab.acme.org');
    }
}
