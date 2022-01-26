<?php declare(strict_types=1);

namespace IonBazan\ComposerDiff\Formatter\Helper;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;

class OutputHelper
{
    public static function strlenWithoutDecoration(OutputFormatterInterface $formatter, string $string): int
    {
        return strlen(static::removeDecoration($formatter, $string));
    }

    public static function removeDecoration(OutputFormatterInterface $formatter, string $string): string
    {
        $isDecorated = $formatter->isDecorated();
        $formatter->setDecorated(false);
        $string = preg_replace("/\033\[[^m]*m/", '', $formatter->format($string));
        $formatter->setDecorated($isDecorated);

        return $string;
    }
}
