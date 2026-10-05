<?php

namespace IonBazan\ComposerDiff\Url;

use Composer\Plugin\Capability\Capability;

interface UrlGeneratorProvider extends Capability
{
    /**
     * @return UrlGenerator[]
     */
    public function getUrlGenerators(): array;
}
