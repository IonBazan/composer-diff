<?php

namespace IonBazan\ComposerDiff\Tests\Util;

use Composer\Composer;
use Composer\Console\Application;
use Composer\IO\IOInterface;

class ComposerApplication extends Application
{
    public function setIO(IOInterface $io)
    {
        $this->io = $io;
    }

    public function setComposer(Composer $composer)
    {
        $this->composer = $composer;
    }

    protected function getDefaultCommands()
    {
        return array();
    }
}
