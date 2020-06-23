<?php

namespace IonBazan\ComposerDiff\Tests;

use IonBazan\ComposerDiff\Command\DiffCommand;
use IonBazan\ComposerDiff\PackageDiff;
use IonBazan\ComposerDiff\Plugin;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    public function testPlugin()
    {
        $composer = $this->getMockBuilder('Composer\Composer')->getMock();
        $io = $this->getMockBuilder('Composer\IO\IOInterface')->getMock();

        $plugin = new Plugin();
        $plugin->activate($composer, $io);
        $command = new DiffCommand(new PackageDiff());

        $this->assertSame(
            array('Composer\Plugin\Capability\CommandProvider' => 'IonBazan\ComposerDiff\Plugin'),
            $plugin->getCapabilities()
        );
        $this->assertEquals(array($command), $plugin->getCommands());
    }
}
