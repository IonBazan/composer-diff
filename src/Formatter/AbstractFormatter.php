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

    /**
     * @return string
     */
    protected function getDecoratedPackageName(DiffEntry $entry)
    {
        return $this->terminalLink($entry->getProjectUrl(), $entry->getPackageName());
    }

    /**
     * @param string|null $url
     * @param string      $title
     *
     * @return string
     */
    private function terminalLink($url, $title)
    {
        if (null === $url) {
            return $title;
        }

        // @phpstan-ignore function.alreadyNarrowedType
        return method_exists('Symfony\Component\Console\Formatter\OutputFormatterStyle', 'setHref') ? sprintf('<href=%s>%s</>', $url, $title) : $title;
    }
}
