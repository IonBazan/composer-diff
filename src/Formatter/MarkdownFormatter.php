<?php declare(strict_types=1);

namespace IonBazan\ComposerDiff\Formatter;

abstract class MarkdownFormatter extends AbstractFormatter
{
    protected function formatUrl(?string $url, string $title): string
    {
        return null !== $url ? sprintf('[%s](%s)', $title, $url) : '';
    }
}
