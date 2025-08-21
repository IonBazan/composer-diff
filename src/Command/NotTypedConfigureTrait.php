<?php

namespace IonBazan\ComposerDiff\Command;

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
