<?php

namespace IonBazan\ComposerDiff\Diff;

use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\Version\VersionParser;

class VersionComparator
{
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
}
