<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Tests\Formatter\Helper;

use IonBazan\ComposerDiff\Formatter\Helper\OutputHelper;
use IonBazan\ComposerDiff\Tests\TestCase;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;

class OutputHelperTest extends TestCase
{
    /**
     * @dataProvider decoratedTextProvider
     */
    public function testItRemovesDecoration(string $expected, string $formatted, string $decorated): void
    {
        $formatter = $this->createMock(OutputFormatterInterface::class);
        $formatter->expects($this->once())->method('isDecorated')->willReturn(true);
        $formatter->expects($this->once())->method('format')->with($decorated)->willReturn($expected);
        $formatter->expects($this->exactly(2))->method('setDecorated')->withConsecutive([false], [true]);

        $this->assertSame($expected, OutputHelper::removeDecoration($formatter, $decorated));
    }

    public function testItSetsPreviousDecorationStatus(): void
    {
        $formatter = $this->createMock(OutputFormatterInterface::class);
        $formatter->expects($this->once())->method('isDecorated')->willReturn(false);
        $formatter->expects($this->once())->method('format')->with('<green>test</green>')->willReturn('test');
        $formatter->expects($this->exactly(2))->method('setDecorated')->withConsecutive([false], [false]);

        $this->assertSame('test', OutputHelper::removeDecoration($formatter, '<green>test</green>'));
    }

    public function decoratedTextProvider()
    {
        yield 'no formatting' => ['test1', 'test1', 'test1'];
        yield 'simple formatting' => ['test1', 'test1', '<fg=green>test1</>'];
        yield 'some formatted characters' => ['test1', "\033[30mtest1", '<fg=green>test1</>'];
    }
}
