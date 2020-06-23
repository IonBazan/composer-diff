<?php

namespace IonBazan\ComposerDiff;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use IonBazan\ComposerDiff\Command\DiffCommand;

class Plugin implements PluginInterface, Capable, CommandProvider
{
    /**
     * @var Composer
     */
    protected $composer;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
    }

    public function getCommands()
    {
        return array(new DiffCommand(new PackageDiff()));
    }

    public function getCapabilities()
    {
        return array(
            'Composer\Plugin\Capability\CommandProvider' => 'IonBazan\ComposerDiff\Plugin',
        );
    }
}
