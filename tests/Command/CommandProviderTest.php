<?php

namespace IonBazan\ComposerDiff\Tests\Command;

use IonBazan\ComposerDiff\Command\CommandProvider;
use IonBazan\ComposerDiff\Command\DiffCommand;
use IonBazan\ComposerDiff\PackageDiff;
use IonBazan\ComposerDiff\Tests\TestCase;

class CommandProviderTest extends TestCase
{
    public function testProvider()
    {
        $composer = $this->getMockBuilder('Composer\Composer')->getMock();
        $config = $this->getMockBuilder('Composer\Config')->disableOriginalConstructor()->getMock();
        $config->expects($this->once())
            ->method('get')
            ->with('gitlab-domains')
            ->willReturn(array());
        $composer->expects($this->once())->method('getConfig')->willReturn($config);
        $provider = new CommandProvider(array('composer' => $composer));
        $this->assertEquals(array(new DiffCommand(new PackageDiff())), $provider->getCommands());
    }
}
