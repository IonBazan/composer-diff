<?php

namespace IonBazan\ComposerDiff\Tests\Diff;

use IonBazan\ComposerDiff\Url\UrlGenerator;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Tests\TestCase;

class DiffEntryTest extends TestCase
{
    /**
     * @dataProvider operationTypeProvider
     */
    public function testOperationTypeGuessing(string $expectedType, OperationInterface $operation): void
    {
        $entry = new DiffEntry($operation);
        $this->assertSame($expectedType, $entry->getType());
        $this->assertTrue($entry->{'is'.ucfirst($expectedType)}());
    }

    public function testToArray(): void
    {
        $operation = new InstallOperation($this->getPackage('a/package-1', '1.0.0'));
        $entry = new DiffEntry($operation);
        $this->assertSame([
            'name' => 'a/package-1',
            'direct' => false,
            'operation' => 'install',
            'version_base' => null,
            'version_target' => '1.0.0',
            'licenses' => [],
            'compare' => null,
            'link' => null,
        ], $entry->toArray());
    }

    public function testIsDirect(): void
    {
        $operation = new InstallOperation($this->getPackage('a/package-1', '1.0.0'));
        $entry = new DiffEntry($operation, null, true);
        $this->assertTrue($entry->isDirect());

        $entry = new DiffEntry($operation, null, false);
        $this->assertFalse($entry->isDirect());
    }

    public function testGetPackage(): void
    {
        $package = $this->getPackage('a/package-1', '1.0.0');
        $operation = new InstallOperation($package);
        $entry = new DiffEntry($operation, null, true);
        $this->assertSame($package, $entry->getPackage());
    }

    /**
     * @dataProvider operationUrlProvider
     */
    public function testUrls(string $expectedUrl, OperationInterface $operation): void
    {
        $urlGenerator = $this->getMockBuilder(UrlGenerator::class)->getMock();
        $urlGenerator->method('getCompareUrl')->willReturn('compare');
        $urlGenerator->method('getProjectUrl')->willReturn('project');
        $urlGenerator->method('getReleaseUrl')->willReturn('release');

        $entry = new DiffEntry($operation, $urlGenerator);
        $this->assertSame($expectedUrl, $entry->getUrl());
        $this->assertSame('project', $entry->getProjectUrl());
    }

    public function testGetUrlReturnsNullForInvalidOperation(): void
    {
        $operation = $this->getMockBuilder(OperationInterface::class)->getMock();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid operation');
        new DiffEntry($operation, $this->getMockBuilder(UrlGenerator::class)->getMock());
    }

    public function testGetProjectUrlReturnsNullForInvalidOperation(): void
    {
        $operation = $this->getMockBuilder(OperationInterface::class)->getMock();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid operation');
        new DiffEntry($operation, $this->getMockBuilder(UrlGenerator::class)->getMock());
    }

    public function testCreateWithoutUrlGenerators(): void
    {
        $operation = $this->getMockBuilder(OperationInterface::class)->getMock();
        $entry = new DiffEntry($operation);

        $this->assertNull($entry->getUrl());
        $this->assertNull($entry->getProjectUrl());
    }

    public function testTypeForInvalidOperation(): void
    {
        $operation = $this->getMockBuilder(OperationInterface::class)->getMock();
        $entry = new DiffEntry($operation);
        $this->assertSame(DiffEntry::TYPE_CHANGE, $entry->getType());
    }

    /**
     * @return iterable<array<mixed>>
     */
    public function operationUrlProvider(): iterable
    {
        return [
            'Install shows release URL' => [
                'release',
                new InstallOperation($this->getPackage('a/package-1', '1.0.0')),
            ],
            'Remove shows release URL' => [
                'release',
                new UninstallOperation($this->getPackage('a/package-1', '1.0.0')),
            ],
            'Upgrade shows compare URL' => [
                'compare',
                new UpdateOperation($this->getPackage('a/package-1', '1.0.0'), $this->getPackage('a/package-1', '2.0.0')),
            ],
            'Downgrade shows compare URL' => [
                'compare',
                new UpdateOperation($this->getPackage('a/package-1', '2.0.0'), $this->getPackage('a/package-1', '1.0.0')),
            ],
            'Change shows compare URL' => [
                'compare',
                new UpdateOperation($this->getPackage('a/package-1', 'dev-master', 'dev-master 1234567'), $this->getPackage('a/package-1', '1.0.0')),
            ],
        ];
    }

    /**
     * @return iterable<array<mixed>>
     */
    public function operationTypeProvider(): iterable
    {
        return [
            'Install operation' => [
                'install',
                new InstallOperation($this->getPackage('a/package-1', '1.0.0')),
            ],
            'Remove operation' => [
                'remove',
                new UninstallOperation($this->getPackage('a/package-1', '1.0.0')),
            ],
            'Upgrade operation' => [
                'upgrade',
                new UpdateOperation($this->getPackage('a/package-1', '1.0.0'), $this->getPackage('a/package-1', '2.0.0')),
            ],
            'Downgrade operation' => [
                'downgrade',
                new UpdateOperation($this->getPackage('a/package-1', '2.0.0'), $this->getPackage('a/package-1', '1.0.0')),
            ],
            'Change operation (base branch)' => [
                'change',
                new UpdateOperation($this->getPackage('a/package-1', 'dev-master', 'dev-master 1234567'), $this->getPackage('a/package-1', '1.0.0')),
            ],
            'Change operation (target branch)' => [
                'change',
                new UpdateOperation($this->getPackage('a/package-1', '1.0.0'), $this->getPackage('a/package-1', 'dev-master', 'dev-master 1234567')),
            ],
            'Change operation (both branch)' => [
                'change',
                new UpdateOperation($this->getPackage('a/package-1', 'dev-master', 'dev-master 7654321'), $this->getPackage('a/package-1', 'dev-master', 'dev-master 1234567')),
            ],
            'Change operation (both custom branch)' => [
                'change',
                new UpdateOperation($this->getPackage('a/package-1', 'dev-develop', 'dev-develop 7654321'), $this->getPackage('a/package-1', 'dev-develop', 'dev-develop 1234567')),
            ],
            'Change operation (target branch and base custom branch)' => [
                'change',
                new UpdateOperation($this->getPackage('a/package-1', 'dev-develop', 'dev-develop 7654321'), $this->getPackage('a/package-1', 'dev-master', 'dev-master 1234567')),
            ],
            'Change operation (base branch and target custom branch)' => [
                'change',
                new UpdateOperation($this->getPackage('a/package-1', 'dev-master', 'dev-master 7654321'), $this->getPackage('a/package-1', 'dev-develop', 'dev-develop 1234567')),
            ],
            'Change operation (target custom branch)' => [
                'change',
                new UpdateOperation($this->getPackage('a/package-1', '1.0.0'), $this->getPackage('a/package-1', 'dev-develop', 'dev-develop 1234567')),
            ],
            'Change operation (base custom branch)' => [
                'change',
                new UpdateOperation($this->getPackage('a/package-1', 'dev-develop', 'dev-develop 7654321'), $this->getPackage('a/package-1', '1.0.0')),
            ],
            'Change operation (BC with Composer 1 master as base)' => [
                'change',
                new UpdateOperation($this->getPackage('a/package-1', 'master', 'master 7654321'), $this->getPackage('a/package-1', '1.0.0')),
            ],
            'Change operation (BC with Composer 1 master as target)' => [
                'change',
                new UpdateOperation($this->getPackage('a/package-1', '1.0.0'), $this->getPackage('a/package-1', 'master', 'master 1234567')),
            ],
        ];
    }
}
