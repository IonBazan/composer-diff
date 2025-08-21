<?php

namespace IonBazan\ComposerDiff\Command;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class_alias(
    PHP_VERSION_ID >= 70200
        ? 'IonBazan\ComposerDiff\Command\TypedConfigureTrait'
        : 'IonBazan\ComposerDiff\Command\NotTypedConfigureTrait',
    'IonBazan\ComposerDiff\Command\ConfigureTrait'
);

/**
 * @codeCoverageIgnore
 *
 * This class contains a typed version of execute() method (PHP 7+).
 */
abstract class BaseTypedCommand extends BaseCommand
{
    use ConfigureTrait;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->handle($input, $output);
    }

    /**
     * @return int
     */
    abstract protected function handle(InputInterface $input, OutputInterface $output);
}
