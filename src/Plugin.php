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

    /**
     * @return void
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommands()
    {
        return array(new DiffCommand(new PackageDiff(), $this->composer->getConfig()->get('gitlab-domains')));
    }

    public function getCapabilities()
    {
        return array(
            'Composer\Plugin\Capability\CommandProvider' => 'IonBazan\ComposerDiff\Plugin',
        );
    }

    /**
     * @return void
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * @return void
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
    }
}
