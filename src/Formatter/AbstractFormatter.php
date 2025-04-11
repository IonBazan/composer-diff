<?php

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Url\GeneratorContainer;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractFormatter implements Formatter
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var GeneratorContainer
     */
    protected $generators;

    public function __construct(OutputInterface $output, GeneratorContainer $generators)
    {
        $this->output = $output;
        $this->generators = $generators;
    }

    /**
     * @return string
     */
    protected function getDecoratedPackageName(DiffEntry $entry)
    {
        $package = $entry->getPackage();

        if (null === $package) {
            return '';
        }

        return $this->terminalLink($entry->getProjectUrl($this->generators), $package->getName());
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
