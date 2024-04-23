<?php

namespace IonBazan\ComposerDiff\Command;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 *
 * This class contains a non-typed version of execute() method (for PHP 5).
 */
abstract class BaseNotTypedCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->handle($input, $output);
    }

    /**
     * @return int
     */
    abstract protected function handle(InputInterface $input, OutputInterface $output);
}
