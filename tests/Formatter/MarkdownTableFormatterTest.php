<?php

namespace IonBazan\ComposerDiff\Tests\Formatter;

use IonBazan\ComposerDiff\Formatter\MarkdownTableFormatter;
use IonBazan\ComposerDiff\Url\GeneratorContainer;
use Symfony\Component\Console\Output\OutputInterface;

class MarkdownTableFormatterTest extends FormatterTest
{
    protected function getSampleOutput($withUrls)
    {
        if ($withUrls) {
            return <<<OUTPUT
| Test        | Base               | Target  | Link                                               |
|-------------|--------------------|---------|----------------------------------------------------|
| a/package-1 | New                | 1.0.0   | [Compare](https://example.com/r/1.0.0)             |
| a/no-link-1 | New                | 1.0.0   |                                                    |
| a/package-2 | 1.0.0              | 1.2.0   | [Compare](https://example.com/c/1.0.0..1.2.0)      |
| a/package-3 | 2.0.0              | 1.1.1   | [Compare](https://example.com/c/2.0.0..1.1.1)      |
| a/no-link-2 | 2.0.0              | 1.1.1   |                                                    |
| a/package-5 | dev-master 1234567 | 1.1.1   | [Compare](https://example.com/c/dev-master..1.1.1) |
| a/package-4 | 0.1.1              | Removed | [Compare](https://example.com/r/0.1.1)             |
| a/no-link-2 | 0.1.1              | Removed |                                                    |


OUTPUT;
        }

        return <<<OUTPUT
| Test        | Base               | Target  |
|-------------|--------------------|---------|
| a/package-1 | New                | 1.0.0   |
| a/no-link-1 | New                | 1.0.0   |
| a/package-2 | 1.0.0              | 1.2.0   |
| a/package-3 | 2.0.0              | 1.1.1   |
| a/no-link-2 | 2.0.0              | 1.1.1   |
| a/package-5 | dev-master 1234567 | 1.1.1   |
| a/package-4 | 0.1.1              | Removed |
| a/no-link-2 | 0.1.1              | Removed |


OUTPUT;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormatter(OutputInterface $output, GeneratorContainer $generators)
    {
        return new MarkdownTableFormatter($output, $generators);
    }
}
