<?php

namespace IonBazan\ComposerDiff\Formatter;

abstract class MarkdownFormatter extends AbstractFormatter
{
    /**
     * @param string|null $url
     * @param string      $title
     *
     * @return string
     */
    protected function formatUrl($url, $title)
    {
        return null !== $url ? sprintf('[%s](%s)', $title, $url) : '';
    }
}
