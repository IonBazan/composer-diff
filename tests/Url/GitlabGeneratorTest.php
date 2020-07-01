<?php

namespace IonBazan\ComposerDiff\Tests\Url;

use IonBazan\ComposerDiff\Url\GitlabGenerator;

class GitlabGeneratorTest extends GeneratorTest
{
    public function releaseUrlProvider()
    {
        return array(
            'with .git' => array(
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.acme.org/acme/package.git'),
                'https://gitlab.acme.org/acme/package/tags/3.12.1',
            ),
            'without .git' => array(
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.acme.org/acme/package'),
                'https://gitlab.acme.org/acme/package/tags/3.12.1',
            ),
            'ssh with .git' => array(
                $this->getPackageWithSource('acme/package', '3.12.1', 'git@gitlab.acme.org:acme/package.git'),
                'https://gitlab.acme.org/acme/package/tags/3.12.1',
            ),
            'ssh without .git' => array(
                $this->getPackageWithSource('acme/package', '3.12.1', 'git@gitlab.acme.org:acme/package'),
                'https://gitlab.acme.org/acme/package/tags/3.12.1',
            ),
            'dev version' => array(
                $this->getPackageWithSource('acme/package', 'dev-master', 'git@gitlab.acme.org:acme/package'),
                null,
            ),
        );
    }

    public function compareUrlProvider()
    {
        return array(
            'same maintainer' => array(
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://gitlab.acme.org/acme/package.git'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.acme.org/acme/package.git'),
                'https://gitlab.acme.org/acme/package/compare/3.12.0...3.12.1',
            ),
            'without .git' => array(
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://gitlab.acme.org/acme/package'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.acme.org/acme/package'),
                'https://gitlab.acme.org/acme/package/compare/3.12.0...3.12.1',
            ),
            'dev versions' => array(
                $this->getPackageWithSource('acme/package', 'dev-master', 'https://gitlab.acme.org/acme/package.git', 'd46283075d76ed244f7825b378eeb1cee246af73'),
                $this->getPackageWithSource('acme/package', 'dev-master', 'https://gitlab.acme.org/acme/package.git', '9b860214d58c48b5cbe99bdb17914d0eb723c9cd'),
                'https://gitlab.acme.org/acme/package/compare/d462830...9b86021',
            ),
            'invalid or short reference' => array(
                $this->getPackageWithSource('acme/package', 'dev-master', 'https://gitlab.acme.org/acme/package.git', 'd462830'),
                $this->getPackageWithSource('acme/package', 'dev-master', 'https://gitlab.acme.org/acme/package.git', '1'),
                'https://gitlab.acme.org/acme/package/compare/d462830...1',
            ),
            'compare with base fork' => array(
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://gitlab.acme.org/IonBazan/package.git'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.acme.org/acme/package.git'),
                'https://gitlab.acme.org/IonBazan/package/compare/3.12.0...acme:3.12.1',
            ),
            'compare with head fork' => array(
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://gitlab.acme.org/acme/package.git'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.acme.org/IonBazan/package.git'),
                'https://gitlab.acme.org/acme/package/compare/3.12.0...IonBazan:3.12.1',
            ),
            'compare with different repository provider' => array(
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://gitlab.acme.org/acme/package.git'),
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
        return new GitlabGenerator('gitlab.acme.org');
    }
}
