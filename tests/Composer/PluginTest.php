<?php

namespace IonBazan\ComposerDiff\Tests\Composer;

use IonBazan\ComposerDiff\Composer\Plugin;
use IonBazan\ComposerDiff\Tests\TestCase;

class PluginTest extends TestCase
{
    public function testPlugin()
    {
        $composer = $this->getMockBuilder('Composer\Composer')->getMock();
        $io = $this->getMockBuilder('Composer\IO\IOInterface')->getMock();

        $plugin = new Plugin();
        $plugin->activate($composer, $io);

        $this->assertSame(
            array('Composer\Plugin\Capability\CommandProvider' => 'IonBazan\ComposerDiff\Command\CommandProvider'),
            $plugin->getCapabilities()
        );
        $plugin->deactivate($composer, $io);
        $plugin->uninstall($composer, $io);
    }
}
