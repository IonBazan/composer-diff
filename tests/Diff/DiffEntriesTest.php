<?php

namespace IonBazan\ComposerDiff\Tests\Diff;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Tests\TestCase;

class DiffEntriesTest extends TestCase
{
    public function testSortedByName(): void
    {
        $entries = new DiffEntries([
            new DiffEntry(new InstallOperation($this->getPackage('b/package', '1.0.0'))),
            new DiffEntry(new UninstallOperation($this->getPackage('a/package', '1.0.0'))),
            new DiffEntry(new InstallOperation($this->getPackage('c/package', '1.0.0'))),
        ]);

        $sorted = $entries->sorted('name');
        $names = array_map(static function (DiffEntry $e) { return $e->getPackageName(); }, $sorted->getArrayCopy());

        $this->assertSame(['a/package', 'b/package', 'c/package'], $names);
    }

    public function testSortedByNameIsDefault(): void
    {
        $entries = new DiffEntries([
            new DiffEntry(new InstallOperation($this->getPackage('b/package', '1.0.0'))),
            new DiffEntry(new UninstallOperation($this->getPackage('a/package', '1.0.0'))),
        ]);

        $sorted = $entries->sorted();
        $names = array_map(static function (DiffEntry $e) { return $e->getPackageName(); }, $sorted->getArrayCopy());

        $this->assertSame(['a/package', 'b/package'], $names);
    }

    public function testSortedByOperation(): void
    {
        $entries = new DiffEntries([
            new DiffEntry(new UninstallOperation($this->getPackage('b/remove', '1.0.0'))),
            new DiffEntry(new InstallOperation($this->getPackage('b/install', '1.0.0'))),
            new DiffEntry(new UpdateOperation($this->getPackage('a/upgrade', '1.0.0'), $this->getPackage('a/upgrade', '2.0.0'))),
            new DiffEntry(new UpdateOperation($this->getPackage('a/downgrade', '2.0.0'), $this->getPackage('a/downgrade', '1.0.0'))),
            new DiffEntry(new InstallOperation($this->getPackage('a/install', '1.0.0'))),
            new DiffEntry(new UninstallOperation($this->getPackage('a/remove', '1.0.0'))),
        ]);

        $sorted = $entries->sorted('operation');
        $names = array_map(static function (DiffEntry $e) { return $e->getPackageName(); }, $sorted->getArrayCopy());

        $this->assertSame([
            'a/install',
            'b/install',
            'a/upgrade',
            'a/downgrade',
            'a/remove',
            'b/remove',
        ], $names);
    }

    public function testSortedDoesNotMutateOriginal(): void
    {
        $entries = new DiffEntries([
            new DiffEntry(new InstallOperation($this->getPackage('b/package', '1.0.0'))),
            new DiffEntry(new InstallOperation($this->getPackage('a/package', '1.0.0'))),
        ]);

        $sorted = $entries->sorted();

        $originalNames = array_map(static function (DiffEntry $e) { return $e->getPackageName(); }, $entries->getArrayCopy());
        $this->assertSame(['b/package', 'a/package'], $originalNames);

        $sortedNames = array_map(static function (DiffEntry $e) { return $e->getPackageName(); }, $sorted->getArrayCopy());
        $this->assertSame(['a/package', 'b/package'], $sortedNames);
    }
}
