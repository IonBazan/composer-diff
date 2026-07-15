<?php

namespace IonBazan\ComposerDiff\Tests\Formatter;

use IonBazan\ComposerDiff\Formatter\Formatter;
use Symfony\Component\Console\Output\OutputInterface;
use IonBazan\ComposerDiff\Formatter\MarkdownListFormatter;
use IonBazan\ComposerDiff\Formatter\JsonFormatter;
use IonBazan\ComposerDiff\Formatter\GitHubFormatter;
use IonBazan\ComposerDiff\Formatter\FormatterContainer;
use IonBazan\ComposerDiff\Formatter\MarkdownTableFormatter;
use IonBazan\ComposerDiff\Tests\TestCase;

class FormatterContainerTest extends TestCase
{
    /**
     * @dataProvider formatterProvider
     *
     * @param class-string<Formatter> $expectedFormatter
     */
    public function testGetFormatter(string $expectedFormatter, string $code): void
    {
        $output = $this->getMockBuilder(OutputInterface::class)->getMock();
        $container = new FormatterContainer($output);

        $this->assertInstanceOf($expectedFormatter, $container->getFormatter($code));
    }

    /**
     * @return iterable<array{0: class-string<Formatter>, 1: string}>
     */
    public static function formatterProvider(): iterable
    {
        return [
            [MarkdownTableFormatter::class, 'mdtable'],
            [MarkdownListFormatter::class, 'mdlist'],
            [JsonFormatter::class, 'json'],
            [GitHubFormatter::class, 'github'],
            [MarkdownTableFormatter::class, 'anything-else'],
        ];
    }
}
