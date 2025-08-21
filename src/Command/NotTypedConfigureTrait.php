<?php

namespace IonBazan\ComposerDiff\Command;

/**
 * @codeCoverageIgnore
 */
trait NotTypedConfigureTrait
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this->doConfigure();
    }
}
