<?php

namespace IonBazan\ComposerDiff\Tests\Diff;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Tests\TestCase;

class DiffEntryTest extends TestCase
{
    /**
     * @param string $expectedType
     *
     * @dataProvider operationTypeProvider
     */
    public function testOperationTypeGuessing($expectedType, OperationInterface $operation)
    {
        $entry = new DiffEntry($operation);
        $this->assertSame($expectedType, $entry->getType());
        $this->assertTrue($entry->{'is'.ucfirst($expectedType)}());
    }

    public function testToArray()
    {
        $operation = new InstallOperation($this->getPackage('a/package-1', '1.0.0'));
        $entry = new DiffEntry($operation);
        $this->assertSame(array(
            'name' => 'a/package-1',
            'direct' => false,
            'operation' => 'install',
            'version_base' => null,
            'version_target' => '1.0.0',
            'licenses' => array(),
            'compare' => null,
            'link' => null,
        ), $entry->toArray());
    }

    public function testIsDirect()
    {
        $operation = new InstallOperation($this->getPackage('a/package-1', '1.0.0'));
        $entry = new DiffEntry($operation, null, true);
        $this->assertTrue($entry->isDirect());

        $entry = new DiffEntry($operation, null, false);
        $this->assertFalse($entry->isDirect());
    }

    public function testGetPackage()
    {
        $package = $this->getPackage('a/package-1', '1.0.0');
        $operation = new InstallOperation($package);
        $entry = new DiffEntry($operation, null, true);
        $this->assertSame($package, $entry->getPackage());
    }

    /**
     * @param string $expectedUrl
     *
     * @dataProvider operationUrlProvider
     */
    public function testUrls($expectedUrl, OperationInterface $operation)
    {
        $urlGenerator = $this->getMockBuilder('IonBazan\ComposerDiff\Url\UrlGenerator')->getMock();
        $urlGenerator->method('getCompareUrl')->willReturn('compare');
        $urlGenerator->method('getProjectUrl')->willReturn('project');
        $urlGenerator->method('getReleaseUrl')->willReturn('release');

        $entry = new DiffEntry($operation, $urlGenerator);
        $this->assertSame($expectedUrl, $entry->getUrl());
        $this->assertSame('project', $entry->getProjectUrl());
    }

    public function testGetUrlReturnsNullForInvalidOperation()
    {
        $operation = $this->getMockBuilder('Composer\DependencyResolver\Operation\OperationInterface')->getMock();
        $this->setExpectedException('InvalidArgumentException', 'Invalid operation');
        new DiffEntry($operation, $this->getMockBuilder('IonBazan\ComposerDiff\Url\UrlGenerator')->getMock());
    }

    public function testGetProjectUrlReturnsNullForInvalidOperation()
    {
        $operation = $this->getMockBuilder('Composer\DependencyResolver\Operation\OperationInterface')->getMock();
        $this->setExpectedException('InvalidArgumentException', 'Invalid operation');
        new DiffEntry($operation, $this->getMockBuilder('IonBazan\ComposerDiff\Url\UrlGenerator')->getMock());
    }

    public function testCreateWithoutUrlGenerators()
    {
        $operation = $this->getMockBuilder('Composer\DependencyResolver\Operation\OperationInterface')->getMock();
        $entry = new DiffEntry($operation);

        $this->assertNull($entry->getUrl());
        $this->assertNull($entry->getProjectUrl());
    }

    public function testTypeForInvalidOperation()
    {
        $operation = $this->getMockBuilder('Composer\DependencyResolver\Operation\OperationInterface')->getMock();
        $entry = new DiffEntry($operation);
        $this->assertSame(DiffEntry::TYPE_CHANGE, $entry->getType());
    }

    public function operationUrlProvider()
    {
        return array(
            'Install shows release URL' => array(
                'release',
                new InstallOperation($this->getPackage('a/package-1', '1.0.0')),
            ),
            'Remove shows release URL' => array(
                'release',
                new UninstallOperation($this->getPackage('a/package-1', '1.0.0')),
            ),
            'Upgrade shows compare URL' => array(
                'compare',
                new UpdateOperation($this->getPackage('a/package-1', '1.0.0'), $this->getPackage('a/package-1', '2.0.0')),
            ),
            'Downgrade shows compare URL' => array(
                'compare',
                new UpdateOperation($this->getPackage('a/package-1', '2.0.0'), $this->getPackage('a/package-1', '1.0.0')),
            ),
            'Change shows compare URL' => array(
                'compare',
                new UpdateOperation($this->getPackage('a/package-1', 'dev-master', 'dev-master 1234567'), $this->getPackage('a/package-1', '1.0.0')),
            ),
        );
    }

    public function operationTypeProvider()
    {
        return array(
            'Install operation' => array(
                'install',
                new InstallOperation($this->getPackage('a/package-1', '1.0.0')),
            ),
            'Remove operation' => array(
                'remove',
                new UninstallOperation($this->getPackage('a/package-1', '1.0.0')),
            ),
            'Upgrade operation' => array(
                'upgrade',
                new UpdateOperation($this->getPackage('a/package-1', '1.0.0'), $this->getPackage('a/package-1', '2.0.0')),
            ),
            'Downgrade operation' => array(
                'downgrade',
                new UpdateOperation($this->getPackage('a/package-1', '2.0.0'), $this->getPackage('a/package-1', '1.0.0')),
            ),
            'Change operation (base branch)' => array(
                'change',
                new UpdateOperation($this->getPackage('a/package-1', 'dev-master', 'dev-master 1234567'), $this->getPackage('a/package-1', '1.0.0')),
            ),
            'Change operation (target branch)' => array(
                'change',
                new UpdateOperation($this->getPackage('a/package-1', '1.0.0'), $this->getPackage('a/package-1', 'dev-master', 'dev-master 1234567')),
            ),
            'Change operation (both branch)' => array(
                'change',
                new UpdateOperation($this->getPackage('a/package-1', 'dev-master', 'dev-master 7654321'), $this->getPackage('a/package-1', 'dev-master', 'dev-master 1234567')),
            ),
            'Change operation (both custom branch)' => array(
                'change',
                new UpdateOperation($this->getPackage('a/package-1', 'dev-develop', 'dev-develop 7654321'), $this->getPackage('a/package-1', 'dev-develop', 'dev-develop 1234567')),
            ),
            'Change operation (target branch and base custom branch)' => array(
                'change',
                new UpdateOperation($this->getPackage('a/package-1', 'dev-develop', 'dev-develop 7654321'), $this->getPackage('a/package-1', 'dev-master', 'dev-master 1234567')),
            ),
            'Change operation (base branch and target custom branch)' => array(
                'change',
                new UpdateOperation($this->getPackage('a/package-1', 'dev-master', 'dev-master 7654321'), $this->getPackage('a/package-1', 'dev-develop', 'dev-develop 1234567')),
            ),
            'Change operation (target custom branch)' => array(
                'change',
                new UpdateOperation($this->getPackage('a/package-1', '1.0.0'), $this->getPackage('a/package-1', 'dev-develop', 'dev-develop 1234567')),
            ),
            'Change operation (base custom branch)' => array(
                'change',
                new UpdateOperation($this->getPackage('a/package-1', 'dev-develop', 'dev-develop 7654321'), $this->getPackage('a/package-1', '1.0.0')),
            ),
            'Change operation (BC with Composer 1 master as base)' => array(
                'change',
                new UpdateOperation($this->getPackage('a/package-1', 'master', 'master 7654321'), $this->getPackage('a/package-1', '1.0.0')),
            ),
            'Change operation (BC with Composer 1 master as target)' => array(
                'change',
                new UpdateOperation($this->getPackage('a/package-1', '1.0.0'), $this->getPackage('a/package-1', 'master', 'master 1234567')),
            ),
        );
    }
}
