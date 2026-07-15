<?php

namespace IonBazan\ComposerDiff\Tests\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use IonBazan\ComposerDiff\Command\CommandProvider as DiffCommandProvider;
use IonBazan\ComposerDiff\Composer\Plugin;
use IonBazan\ComposerDiff\Tests\TestCase;

class PluginTest extends TestCase
{
    public function testPlugin(): void
    {
        $composer = $this->getMockBuilder(Composer::class)->getMock();
        $io = $this->getMockBuilder(IOInterface::class)->getMock();

        $plugin = new Plugin();
        $plugin->activate($composer, $io);

        $this->assertSame(
            [CommandProvider::class => DiffCommandProvider::class],
            $plugin->getCapabilities()
        );
        $plugin->deactivate($composer, $io);
        $plugin->uninstall($composer, $io);
    }
}
