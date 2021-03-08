<?php

namespace IonBazan\ComposerDiff\Formatter\Helper;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;

class OutputHelper
{
    /**
     * @param string $string
     *
     * @return int
     */
    public static function strlenWithoutDecoration(OutputFormatterInterface $formatter, $string)
    {
        return strlen(static::removeDecoration($formatter, $string));
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function removeDecoration(OutputFormatterInterface $formatter, $string)
    {
        $isDecorated = $formatter->isDecorated();
        $formatter->setDecorated(false);
        $string = preg_replace("/\033\[[^m]*m/", '', $formatter->format($string));
        $formatter->setDecorated($isDecorated);

        return $string;
    }
}
