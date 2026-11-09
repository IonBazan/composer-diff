<?php

namespace IonBazan\ComposerDiff\Diff;

use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\PackageInterface;
use Composer\Package\Version\VersionParser;

class VersionComparator
{
    /**
     * Package metadata which identifies the actual contents of a package, in order of precedence.
     */
    const REFERENCE_FIELDS = [
        'ref' => 'getSourceReference',
        'dist-ref' => 'getDistReference',
        'sha1' => 'getDistSha1Checksum',
    ];

    /**
     * @return bool|null true if it's upgrade, false if it's downgrade, null if it is a change
     */
    public static function isUpgrade(UpdateOperation $operation): ?bool
    {
        $versionParser = new VersionParser();
        try {
            $normalizedFrom = $versionParser->normalize($operation->getInitialPackage()->getVersion());
            $normalizedTo = $versionParser->normalize($operation->getTargetPackage()->getVersion());
        } catch (\UnexpectedValueException $e) {
            return null; // Consider as change if versions are not parseable
        }

        /* @infection-ignore-all False-positive, handled by build matrix with Composer 1 installed */
        if (
            VersionParser::DEFAULT_BRANCH_ALIAS === $normalizedFrom
            || VersionParser::DEFAULT_BRANCH_ALIAS === $normalizedTo // BC for Composer 1.x
            || 0 === strpos($normalizedFrom, 'dev-')
            || 0 === strpos($normalizedTo, 'dev-')
        ) {
            return null;
        }

        return VersionParser::isUpgrade($normalizedFrom, $normalizedTo);
    }

    /**
     * Finds the first reference which differs between two packages.
     * Fields missing on either side are ignored, as they cannot be compared.
     *
     * @return array{string, string, string}|null label, base value and target value, or null if nothing differs
     */
    public static function getReferenceDifference(PackageInterface $base, PackageInterface $target): ?array
    {
        foreach (self::REFERENCE_FIELDS as $label => $getter) {
            $baseValue = $base->{$getter}();
            $targetValue = $target->{$getter}();

            if (!$baseValue || !$targetValue || $baseValue === $targetValue) {
                continue;
            }

            return [$label, $baseValue, $targetValue];
        }

        return null;
    }
}
