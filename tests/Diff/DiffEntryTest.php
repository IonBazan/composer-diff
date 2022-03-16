<?php

declare(strict_types=1);

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
     * @dataProvider operationTypeProvider
     */
    public function testOperationTypeGuessing(string $expectedType, OperationInterface $operation): void
    {
        $entry = new DiffEntry($operation);
        $this->assertSame($expectedType, $entry->getType());
        $this->assertTrue($entry->{'is'.ucfirst($expectedType)}());
    }

    public function operationTypeProvider(): iterable
    {
        yield 'Install operation' => [
            'install',
            new InstallOperation($this->getPackage('a/package-1', '1.0.0')),
        ];
        yield 'Remove operation' => [
            'remove',
            new UninstallOperation($this->getPackage('a/package-1', '1.0.0')),
        ];
        yield 'Upgrade operation' => [
            'upgrade',
            new UpdateOperation($this->getPackage('a/package-1', '1.0.0'), $this->getPackage('a/package-1', '2.0.0')),
        ];
        yield 'Downgrade operation' => [
            'downgrade',
            new UpdateOperation($this->getPackage('a/package-1', '2.0.0'), $this->getPackage('a/package-1', '1.0.0')),
        ];
        yield 'Change operation (base branch)' => [
            'change',
            new UpdateOperation($this->getPackage('a/package-1', 'dev-master', 'dev-master 1234567'), $this->getPackage('a/package-1', '1.0.0')),
        ];
        yield 'Change operation (target branch)' => [
            'change',
            new UpdateOperation($this->getPackage('a/package-1', '1.0.0'), $this->getPackage('a/package-1', 'dev-master', 'dev-master 1234567')),
        ];
        yield 'Change operation (both branch)' => [
            'change',
            new UpdateOperation($this->getPackage('a/package-1', 'dev-master', 'dev-master 7654321'), $this->getPackage('a/package-1', 'dev-master', 'dev-master 1234567')),
        ];
        yield 'Change operation (both custom branch)' => [
            'change',
            new UpdateOperation($this->getPackage('a/package-1', 'dev-develop', 'dev-develop 7654321'), $this->getPackage('a/package-1', 'dev-develop', 'dev-develop 1234567')),
        ];
        yield 'Change operation (target branch and base custom branch)' => [
            'change',
            new UpdateOperation($this->getPackage('a/package-1', 'dev-develop', 'dev-develop 7654321'), $this->getPackage('a/package-1', 'dev-master', 'dev-master 1234567')),
        ];
        yield 'Change operation (base branch and target custom branch)' => [
            'change',
            new UpdateOperation($this->getPackage('a/package-1', 'dev-master', 'dev-master 7654321'), $this->getPackage('a/package-1', 'dev-develop', 'dev-develop 1234567')),
        ];
        yield 'Change operation (target custom branch)' => [
            'change',
            new UpdateOperation($this->getPackage('a/package-1', '1.0.0'), $this->getPackage('a/package-1', 'dev-develop', 'dev-develop 1234567')),
        ];
        yield 'Change operation (base custom branch)' => [
            'change',
            new UpdateOperation($this->getPackage('a/package-1', 'dev-develop', 'dev-develop 7654321'), $this->getPackage('a/package-1', '1.0.0')),
        ];
        yield 'Change operation (BC with Composer 1 master as base)' => [
            'change',
            new UpdateOperation($this->getPackage('a/package-1', 'master', 'master 7654321'), $this->getPackage('a/package-1', '1.0.0')),
        ];
        yield 'Change operation (BC with Composer 1 master as target)' => [
            'change',
            new UpdateOperation($this->getPackage('a/package-1', '1.0.0'), $this->getPackage('a/package-1', 'master', 'master 1234567')),
        ];
    }
}
