<?php

namespace IonBazan\ComposerDiff\Tests\Url;

use IonBazan\ComposerDiff\Url\BitBucketGenerator;

class BitBucketGeneratorTest extends GeneratorTest
{
    public function releaseUrlProvider()
    {
        return array(
            'with .git' => array(
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://bitbucket.org/acme/package.git'),
                'https://bitbucket.org/acme/package/src/3.12.1',
            ),
            'without .git' => array(
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://bitbucket.org/acme/package'),
                'https://bitbucket.org/acme/package/src/3.12.1',
            ),
            'ssh with .git' => array(
                $this->getPackageWithSource('acme/package', '3.12.1', 'git@bitbucket.org:acme/package.git'),
                'https://bitbucket.org/acme/package/src/3.12.1',
            ),
            'ssh without .git' => array(
                $this->getPackageWithSource('acme/package', '3.12.1', 'git@bitbucket.org:acme/package'),
                'https://bitbucket.org/acme/package/src/3.12.1',
            ),
            'dev version' => array(
                $this->getPackageWithSource('acme/package', 'dev-master', 'git@bitbucket.org:acme/package', 'd46283075d76ed244f7825b378eeb1cee246af73'),
                'https://bitbucket.org/acme/package/src/d46283075d76ed244f7825b378eeb1cee246af73',
            ),
        );
    }

    public function projectUrlProvider()
    {
        return array(
            'with .git' => array(
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://bitbucket.org/acme/package.git'),
                'https://bitbucket.org/acme/package',
            ),
            'without .git' => array(
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://bitbucket.org/acme/package'),
                'https://bitbucket.org/acme/package',
            ),
            'ssh with .git' => array(
                $this->getPackageWithSource('acme/package', '3.12.1', 'git@bitbucket.org:acme/package.git'),
                'https://bitbucket.org/acme/package',
            ),
            'ssh without .git' => array(
                $this->getPackageWithSource('acme/package', '3.12.1', 'git@bitbucket.org:acme/package'),
                'https://bitbucket.org/acme/package',
            ),
            'dev version' => array(
                $this->getPackageWithSource('acme/package', 'dev-master', 'git@bitbucket.org:acme/package', 'd46283075d76ed244f7825b378eeb1cee246af73'),
                'https://bitbucket.org/acme/package',
            ),
        );
    }

    public function compareUrlProvider()
    {
        return array(
            'same maintainer' => array(
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://bitbucket.org/acme/package.git'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://bitbucket.org/acme/package.git'),
                'https://bitbucket.org/acme/package/branches/compare/3.12.0%0D3.12.1',
            ),
            'without .git' => array(
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://bitbucket.org/acme/package'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://bitbucket.org/acme/package'),
                'https://bitbucket.org/acme/package/branches/compare/3.12.0%0D3.12.1',
            ),
            'dev versions' => array(
                $this->getPackageWithSource('acme/package', 'dev-master', 'https://bitbucket.org/acme/package.git', 'd46283075d76ed244f7825b378eeb1cee246af73'),
                $this->getPackageWithSource('acme/package', 'dev-master', 'https://bitbucket.org/acme/package.git', '9b860214d58c48b5cbe99bdb17914d0eb723c9cd'),
                'https://bitbucket.org/acme/package/branches/compare/d462830%0D9b86021',
            ),
            'invalid or short reference' => array(
                $this->getPackageWithSource('acme/package', 'dev-master', 'https://bitbucket.org/acme/package.git', 'd462830'),
                $this->getPackageWithSource('acme/package', 'dev-master', 'https://bitbucket.org/acme/package.git', '1'),
                'https://bitbucket.org/acme/package/branches/compare/d462830%0D1',
            ),
            'compare with base fork' => array(
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://bitbucket.org/IonBazan/package.git'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://bitbucket.org/acme/package.git'),
                'https://bitbucket.org/acme/package/branches/compare/IonBazan/package:3.12.0%0Dacme/package:3.12.1',
            ),
            'compare with head fork' => array(
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://bitbucket.org/acme/package.git'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://bitbucket.org/IonBazan/package.git'),
                'https://bitbucket.org/IonBazan/package/branches/compare/acme/package:3.12.0%0DIonBazan/package:3.12.1',
            ),
            'compare with different repository provider' => array(
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://bitbucket.org/acme/package.git'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.org/acme/package.git'),
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
