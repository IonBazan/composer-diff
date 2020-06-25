<?php

namespace IonBazan\ComposerDiff\Tests\Formatter;

use IonBazan\ComposerDiff\Formatter\MarkdownTableFormatter;
use Symfony\Component\Console\Output\OutputInterface;

class MarkdownTableFormatterTest extends FormatterTest
{
    protected function getSampleOutput()
    {
        return <<<OUTPUT
| Test        | Base  | Target  |
|-------------|-------|---------|
| a/package-1 | New   | 1.0.0   |
| a/package-2 | 1.0.0 | 1.2.0   |
| a/package-3 | 0.1.1 | Removed |


OUTPUT;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormatter(OutputInterface $output)
    {
        return new MarkdownTableFormatter($output);
    }
}
