<?php

namespace IonBazan\ComposerDiff\Tests\Formatter;

use IonBazan\ComposerDiff\Formatter\FormatterContainer;
use IonBazan\ComposerDiff\Tests\TestCase;

class FormatterContainerTest extends TestCase
{
    /**
     * @dataProvider formatterProvider
     */
    public function testGetFormatter($expectedFormatter, $code)
    {
        $output = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')->getMock();
        $generators = $this->getMockBuilder('IonBazan\ComposerDiff\Url\GeneratorContainer')->getMock();
        $container = new FormatterContainer($output, $generators);

        $this->assertInstanceOf($expectedFormatter, $container->getFormatter($code));
    }

    public static function formatterProvider()
    {
        return array(
            array('IonBazan\ComposerDiff\Formatter\MarkdownTableFormatter', 'mdtable'),
            array('IonBazan\ComposerDiff\Formatter\MarkdownListFormatter', 'mdlist'),
            array('IonBazan\ComposerDiff\Formatter\JsonFormatter', 'json'),
            array('IonBazan\ComposerDiff\Formatter\GitHubFormatter', 'github'),
            array('IonBazan\ComposerDiff\Formatter\MarkdownTableFormatter', 'anything-else'),
        );
    }
}
