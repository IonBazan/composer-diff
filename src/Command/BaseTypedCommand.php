<?php

namespace IonBazan\ComposerDiff\Command;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 *
 * This class contains a typed version of execute() method (PHP 7+).
 */
abstract class BaseTypedCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->handle($input, $output);
    }

    /**
     * @return int
     */
    abstract protected function handle(InputInterface $input, OutputInterface $output);
}
