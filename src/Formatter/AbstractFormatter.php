<?php

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Diff\DiffEntry;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractFormatter implements Formatter
{
    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    protected function getDecoratedPackageName(DiffEntry $entry): string
    {
        return $this->terminalLink($entry->getProjectUrl(), $entry->getPackageName());
    }

    private function terminalLink(?string $url, string $title): string
    {
        if (null === $url) {
            return $title;
        }

        return sprintf('<href=%s>%s</>', $url, $title);
    }
}
