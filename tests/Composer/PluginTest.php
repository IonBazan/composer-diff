<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Tests\Composer;

use IonBazan\ComposerDiff\Composer\Plugin;
use IonBazan\ComposerDiff\Tests\TestCase;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;

class PluginTest extends TestCase
{
    public function testPlugin(): void
    {
        $composer = $this->createMock(Composer::class);
        $io = $this->createMock(IOInterface::class);

        $plugin = new Plugin();
        $plugin->activate($composer, $io);

        $this->assertSame(
            [CommandProvider::class => \IonBazan\ComposerDiff\Command\CommandProvider::class],
            $plugin->getCapabilities()
        );
        $plugin->deactivate($composer, $io);
        $plugin->uninstall($composer, $io);
    }
}
