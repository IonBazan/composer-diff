<?php

namespace IonBazan\ComposerDiff\Tests\Diff;

use Composer\Package\Package;
use Composer\Package\PackageInterface;
use IonBazan\ComposerDiff\Diff\VersionComparator;
use IonBazan\ComposerDiff\Tests\TestCase;

class VersionComparatorTest extends TestCase
{
    /**
     * @dataProvider referenceDifferenceProvider
     */
    public function testGetReferenceDifference(?string $expected, PackageInterface $base, PackageInterface $target): void
    {
        $this->assertSame($expected, VersionComparator::getReferenceDifference($base, $target));
    }

    public function testGetReferenceDifferenceWithCustomFields(): void
    {
        $fields = ['sha1' => 'getDistSha1Checksum'];

        $this->assertSame(
            'sha1',
            VersionComparator::getReferenceDifference($this->createPackage('abc', 'def', 'ghi'), $this->createPackage('xyz', 'xyz', 'xyz'), $fields)
        );
        $this->assertNull(VersionComparator::getReferenceDifference($this->createPackage('abc', 'def', 'ghi'), $this->createPackage('xyz', 'xyz', 'ghi'), $fields));
    }

    /**
     * @return iterable<array<mixed>>
     */
    public function referenceDifferenceProvider(): iterable
    {
        return [
            'nothing set' => [
                null,
                $this->createPackage(null, null, null),
                $this->createPackage(null, null, null),
            ],
            'identical references' => [
                null,
                $this->createPackage('abc', 'def', 'ghi'),
                $this->createPackage('abc', 'def', 'ghi'),
            ],
            'source reference changed' => [
                'ref',
                $this->createPackage('abc', 'def', 'ghi'),
                $this->createPackage('xyz', 'def', 'ghi'),
            ],
            'dist reference changed' => [
                'dist-ref',
                $this->createPackage('abc', 'def', 'ghi'),
                $this->createPackage('abc', 'xyz', 'ghi'),
            ],
            'dist checksum changed is ignored by default' => [
                null,
                $this->createPackage('abc', 'def', 'ghi'),
                $this->createPackage('abc', 'def', 'xyz'),
            ],
            'source reference takes precedence' => [
                'ref',
                $this->createPackage('abc', 'def', 'ghi'),
                $this->createPackage('xyz', 'xyz', 'xyz'),
            ],
            'dist reference changed along with checksum' => [
                'dist-ref',
                $this->createPackage('abc', 'def', 'ghi'),
                $this->createPackage('abc', 'xyz', 'xyz'),
            ],
            'ignores references missing from base' => [
                null,
                $this->createPackage(null, null, null),
                $this->createPackage('abc', 'def', 'ghi'),
            ],
            'ignores references missing from target' => [
                null,
                $this->createPackage('abc', 'def', 'ghi'),
                $this->createPackage(null, null, null),
            ],
            'ignores empty references' => [
                null,
                $this->createPackage('', '', ''),
                $this->createPackage('abc', 'def', 'ghi'),
            ],
            'falls through to next comparable reference' => [
                'dist-ref',
                $this->createPackage(null, 'def', 'ghi'),
                $this->createPackage('abc', 'xyz', 'ghi'),
            ],
        ];
    }

    private function createPackage(?string $sourceReference, ?string $distReference, ?string $checksum): PackageInterface
    {
        $package = new Package('a/package-1', '1.0.0.0', '1.0.0');
        $package->setSourceReference($sourceReference);
        $package->setDistReference($distReference);
        $package->setDistSha1Checksum($checksum);

        return $package;
    }
}
