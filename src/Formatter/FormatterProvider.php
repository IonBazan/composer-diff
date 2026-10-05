<?php

namespace IonBazan\ComposerDiff\Formatter;

use Composer\Plugin\Capability\Capability;
use Symfony\Component\Console\Output\OutputInterface;

interface FormatterProvider extends Capability
{
    /**
     * @return array<string, Formatter> Formatters keyed by the name used in the --format option
     */
    public function getFormatters(OutputInterface $output): array;
}
