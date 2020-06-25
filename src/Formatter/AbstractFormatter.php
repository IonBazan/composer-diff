<?php

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Semver\Semver;
use Composer\Semver\VersionParser;

abstract class AbstractFormatter implements Formatter
{
    protected static function isUpgrade(UpdateOperation $operation)
    {
        $versionParser = new VersionParser();
        $normalizedFrom = $versionParser->normalize($operation->getInitialPackage()->getFullPrettyVersion());
        $normalizedTo = $versionParser->normalize($operation->getTargetPackage()->getFullPrettyVersion());

        $sorted = Semver::sort(array($normalizedTo, $normalizedFrom));

        return $sorted[0] === $normalizedFrom;
    }
}
