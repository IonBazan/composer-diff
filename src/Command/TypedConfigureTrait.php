<?php

namespace IonBazan\ComposerDiff\Command;

trait TypedConfigureTrait
{
    protected function configure(): void
    {
        $this->doConfigure();
    }
}
