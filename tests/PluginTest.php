<?php

namespace IonBazan\ComposerDiff\Tests;

use IonBazan\ComposerDiff\Command\DiffCommand;
use IonBazan\ComposerDiff\Plugin;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    public function testPlugin()
    {
        $composer = $this->createMock('Composer\Composer');
        $io = $this->createMock('Composer\IO\IOInterface');

        $plugin = new Plugin();
        $plugin->activate($composer, $io);

        $this->assertSame(
            array('Composer\Plugin\Capability\CommandProvider' => 'IonBazan\ComposerDiff\Plugin'),
            $plugin->getCapabilities()
        );
        $this->assertEquals(array(new DiffCommand()), $plugin->getCommands());
    }
}
