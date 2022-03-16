<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Tests\Command;

use IonBazan\ComposerDiff\Command\CommandProvider;
use IonBazan\ComposerDiff\Command\DiffCommand;
use IonBazan\ComposerDiff\PackageDiff;
use IonBazan\ComposerDiff\Tests\TestCase;
use Composer\Composer;
use Composer\Config;

class CommandProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $composer = $this->createMock(Composer::class);
        $config = $this->createMock(Config::class);
        $config->expects($this->once())
            ->method('get')
            ->with('gitlab-domains')
            ->willReturn([]);
        $composer->expects($this->once())->method('getConfig')->willReturn($config);
        $provider = new CommandProvider(['composer' => $composer]);
        $this->assertEquals([new DiffCommand(new PackageDiff())], $provider->getCommands());
    }
}
