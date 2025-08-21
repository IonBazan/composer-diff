<?php

namespace IonBazan\ComposerDiff\Command;

/**
 * @codeCoverageIgnore
 */
trait TypedConfigureTrait
{
    protected function configure(): void
    {
        $this->doConfigure();
    }
}
