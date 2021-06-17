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
