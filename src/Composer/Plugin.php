<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\Capability\CommandProvider;
use IonBazan\ComposerDiff\Command\CommandProvider as DiffCommandProvider;

class Plugin implements PluginInterface, Capable
{
    /**
     * @var Composer
     */
    protected $composer;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
    }

    public function getCapabilities(): array
    {
        return [
            CommandProvider::class => DiffCommandProvider::class,
        ];
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }
}
