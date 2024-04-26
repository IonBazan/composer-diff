<?php

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Url\GeneratorContainer;
use Symfony\Component\Console\Output\OutputInterface;

class FormatterContainer
{
    const DEFAULT_FORMATTER = 'mdtable';

    /**
     * @var array<string, Formatter>
     */
    private $formatters;

    public function __construct(OutputInterface $output, GeneratorContainer $generators)
    {
        $this->formatters = array(
            'mdtable' => new MarkdownTableFormatter($output, $generators),
            'mdlist' => new MarkdownListFormatter($output, $generators),
            'github' => new GitHubFormatter($output, $generators),
            'json' => new JsonFormatter($output, $generators),
        );
    }

    /**
     * @param string $name
     *
     * @return Formatter
     */
    public function getFormatter($name)
    {
        if (!isset($this->formatters[$name])) {
            return $this->formatters[self::DEFAULT_FORMATTER];
        }

        return $this->formatters[$name];
    }
}
