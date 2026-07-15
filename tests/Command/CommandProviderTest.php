<?php

namespace IonBazan\ComposerDiff\Tests\Command;

use Composer\Composer;
use Composer\Config;
use IonBazan\ComposerDiff\Command\CommandProvider;
use IonBazan\ComposerDiff\Command\DiffCommand;
use IonBazan\ComposerDiff\PackageDiff;
use IonBazan\ComposerDiff\Tests\TestCase;

class CommandProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $composer = $this->getMockBuilder(Composer::class)->getMock();
        $config = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $config->expects($this->once())
            ->method('get')
            ->with('gitlab-domains')
            ->willReturn([]);
        $composer->expects($this->once())->method('getConfig')->willReturn($config);
        $provider = new CommandProvider(['composer' => $composer]);
        $this->assertEquals([new DiffCommand(new PackageDiff())], $provider->getCommands());
    }
}
