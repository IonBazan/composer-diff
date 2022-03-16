<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Tests\Formatter;

use IonBazan\ComposerDiff\Formatter\Formatter;
use IonBazan\ComposerDiff\Formatter\MarkdownTableFormatter;
use IonBazan\ComposerDiff\Url\GeneratorContainer;
use Symfony\Component\Console\Output\OutputInterface;

class MarkdownTableFormatterTest extends FormatterTest
{
    protected function getSampleOutput(bool $withUrls): string
    {
        if ($withUrls) {
            return <<<OUTPUT
| Prod Packages                                    | Operation  | Base    | Target | Link                                          |
|--------------------------------------------------|------------|---------|--------|-----------------------------------------------|
| [a/package-1](https://example.com/r/a/package-1) | New        | -       | 1.0.0  | [Compare](https://example.com/r/1.0.0)        |
| a/no-link-1                                      | New        | -       | 1.0.0  |                                               |
| [a/package-2](https://example.com/r/a/package-2) | Upgraded   | 1.0.0   | 1.2.0  | [Compare](https://example.com/c/1.0.0..1.2.0) |
| [a/package-3](https://example.com/r/a/package-3) | Downgraded | 2.0.0   | 1.1.1  | [Compare](https://example.com/c/2.0.0..1.1.1) |
| a/no-link-2                                      | Downgraded | 2.0.0   | 1.1.1  |                                               |
| php                                              | Changed    | >=7.4.6 | ^8.0   |                                               |

| Dev Packages                                     | Operation | Base               | Target | Link                                               |
|--------------------------------------------------|-----------|--------------------|--------|----------------------------------------------------|
| [a/package-5](https://example.com/r/a/package-5) | Changed   | dev-master 1234567 | 1.1.1  | [Compare](https://example.com/c/dev-master..1.1.1) |
| [a/package-4](https://example.com/r/a/package-4) | Removed   | 0.1.1              | -      | [Compare](https://example.com/r/0.1.1)             |
| a/no-link-2                                      | Removed   | 0.1.1              | -      |                                                    |


OUTPUT;
        }

        return <<<OUTPUT
| Prod Packages | Operation  | Base    | Target |
|---------------|------------|---------|--------|
| a/package-1   | New        | -       | 1.0.0  |
| a/no-link-1   | New        | -       | 1.0.0  |
| a/package-2   | Upgraded   | 1.0.0   | 1.2.0  |
| a/package-3   | Downgraded | 2.0.0   | 1.1.1  |
| a/no-link-2   | Downgraded | 2.0.0   | 1.1.1  |
| php           | Changed    | >=7.4.6 | ^8.0   |

| Dev Packages | Operation | Base               | Target |
|--------------|-----------|--------------------|--------|
| a/package-5  | Changed   | dev-master 1234567 | 1.1.1  |
| a/package-4  | Removed   | 0.1.1              | -      |
| a/no-link-2  | Removed   | 0.1.1              | -      |


OUTPUT;
    }

    protected function getFormatter(OutputInterface $output, GeneratorContainer $generators): Formatter
    {
        return new MarkdownTableFormatter($output, $generators);
    }
}
