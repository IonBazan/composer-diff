<?php

namespace IonBazan\ComposerDiff\Tests\Formatter\Helper;

use IonBazan\ComposerDiff\Formatter\Helper\OutputHelper;
use IonBazan\ComposerDiff\Tests\TestCase;

class OutputHelperTest extends TestCase
{
    /**
     * @param string $expected
     * @param string $decorated
     *
     * @dataProvider decoratedTextProvider
     */
    public function testItRemovesDecoration($expected, $formatted, $decorated)
    {
        $formatter = $this->getMockBuilder('Symfony\Component\Console\Formatter\OutputFormatterInterface')->getMock();
        $formatter->expects($this->once())->method('isDecorated')->willReturn(true);
        $formatter->expects($this->once())->method('format')->with($decorated)->willReturn($expected);
        $formatter->expects($this->exactly(2))->method('setDecorated')->withConsecutive(array(false), array(true));

        $this->assertSame($expected, OutputHelper::removeDecoration($formatter, $decorated));
    }

    public function testItSetsPreviousDecorationStatus()
    {
        $formatter = $this->getMockBuilder('Symfony\Component\Console\Formatter\OutputFormatterInterface')->getMock();
        $formatter->expects($this->once())->method('isDecorated')->willReturn(false);
        $formatter->expects($this->once())->method('format')->with('<green>test</green>')->willReturn('test');
        $formatter->expects($this->exactly(2))->method('setDecorated')->withConsecutive(array(false), array(false));

        $this->assertSame('test', OutputHelper::removeDecoration($formatter, '<green>test</green>'));
    }

    public function decoratedTextProvider()
    {
        return array(
            'no formatting' => array('test1', 'test1', 'test1'),
            'simple formatting' => array('test1', 'test1', '<fg=green>test1</>'),
            'some formatted characters' => array('test1', "\033[30mtest1", '<fg=green>test1</>'),
        );
    }
}
