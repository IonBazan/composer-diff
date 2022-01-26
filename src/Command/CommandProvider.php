<?php declare(strict_types=1);

namespace IonBazan\ComposerDiff\Command;

use Composer\Composer;
use Composer\Plugin\Capability\CommandProvider as BaseCommandProvider;
use IonBazan\ComposerDiff\PackageDiff;

class CommandProvider implements BaseCommandProvider
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * @param array{composer:Composer} $args
     */
    public function __construct(array $args)
    {
        $this->composer = $args['composer'];
    }

    public function getCommands(): array
    {
        return [new DiffCommand(new PackageDiff(), $this->composer->getConfig()->get('gitlab-domains'))];
    }
}
