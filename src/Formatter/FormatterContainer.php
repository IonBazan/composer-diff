<?php

namespace IonBazan\ComposerDiff\Formatter;

use Symfony\Component\Console\Output\OutputInterface;

class FormatterContainer
{
    const DEFAULT_FORMATTER = 'mdtable';

    /**
     * @var array<string, Formatter>
     */
    private $formatters;

    public function __construct(OutputInterface $output)
    {
        $this->formatters = array(
            'mdtable' => new MarkdownTableFormatter($output),
            'mdlist' => new MarkdownListFormatter($output),
            'github' => new GitHubFormatter($output),
            'json' => new JsonFormatter($output),
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
            throw new \InvalidArgumentException(sprintf('Invalid format "%s". Supported formats: %s', $name, implode(', ', array_keys($this->formatters))));
        }

        return $this->formatters[$name];
    }
}
