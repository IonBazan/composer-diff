<?php

namespace IonBazan\ComposerDiff\Tests\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Formatter\CsvFormatter;
use IonBazan\ComposerDiff\Formatter\Formatter;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class CsvFormatterTest extends FormatterTest
{
    public function testRenderSingle(): void
    {
        $stream = fopen('php://memory', 'wb', false);
        assert(false !== $stream);
        $output = new StreamOutput($stream);
        $entries = new DiffEntries([
            new DiffEntry(new InstallOperation($this->getCompletePackage('a/package-1', '1.0.0', null, ['Quoted "license"', "Multi\nline", "Carriage\rreturn", '<info>Tagged</info>'])), null, true),
        ]);
        $this->getFormatter($output)->renderSingle($entries, 'test', false, true);

        $this->assertSame(
            'section,name,direct,operation,version_base,version_target,licenses'.PHP_EOL.
            "test,a/package-1,true,install,,1.0.0,\"Quoted \"\"license\"\", Multi\nline, Carriage\rreturn, <info>Tagged</info>\"".PHP_EOL,
            $this->getDisplay($output)
        );
    }

    protected function getFormatter(OutputInterface $output): Formatter
    {
        return new CsvFormatter($output);
    }

    protected static function getEmptyOutput(): string
    {
        return 'section,name,direct,operation,version_base,version_target,compare,link'.PHP_EOL;
    }

    protected function getSampleOutput(bool $withUrls, bool $withLicenses, bool $decorated): string
    {
        if ($withUrls && $withLicenses) {
            return <<<OUTPUT
section,name,direct,operation,version_base,version_target,licenses,compare,link
prod,a/package-1,false,install,,1.0.0,,https://example.com/r/1.0.0,https://example.com/r/a/package-1
prod,a/no-link-1,false,install,,1.0.0,,,
prod,a/package-2,false,upgrade,1.0.0,1.2.0,,https://example.com/c/1.0.0..1.2.0,https://example.com/r/a/package-2
prod,a/package-3,false,downgrade,2.0.0,1.1.1,,https://example.com/c/2.0.0..1.1.1,https://example.com/r/a/package-3
prod,a/no-link-2,false,downgrade,2.0.0,1.1.1,,,
prod,php,false,change,>=7.4.6,^8.0,,,
dev,a/package-5,false,change,"dev-master 1234567",1.1.1,,https://example.com/c/dev-master..1.1.1,https://example.com/r/a/package-5
dev,a/package-4,false,remove,0.1.1,,"MIT, BSD-3-Clause",https://example.com/r/0.1.1,https://example.com/r/a/package-4
dev,a/no-link-2,false,remove,0.1.1,,MIT,,

OUTPUT;
        }

        if ($withUrls) {
            return <<<OUTPUT
section,name,direct,operation,version_base,version_target,compare,link
prod,a/package-1,false,install,,1.0.0,https://example.com/r/1.0.0,https://example.com/r/a/package-1
prod,a/no-link-1,false,install,,1.0.0,,
prod,a/package-2,false,upgrade,1.0.0,1.2.0,https://example.com/c/1.0.0..1.2.0,https://example.com/r/a/package-2
prod,a/package-3,false,downgrade,2.0.0,1.1.1,https://example.com/c/2.0.0..1.1.1,https://example.com/r/a/package-3
prod,a/no-link-2,false,downgrade,2.0.0,1.1.1,,
prod,php,false,change,>=7.4.6,^8.0,,
dev,a/package-5,false,change,"dev-master 1234567",1.1.1,https://example.com/c/dev-master..1.1.1,https://example.com/r/a/package-5
dev,a/package-4,false,remove,0.1.1,,https://example.com/r/0.1.1,https://example.com/r/a/package-4
dev,a/no-link-2,false,remove,0.1.1,,,

OUTPUT;
        }

        if ($withLicenses) {
            return <<<OUTPUT
section,name,direct,operation,version_base,version_target,licenses
prod,a/package-1,false,install,,1.0.0,
prod,a/no-link-1,false,install,,1.0.0,
prod,a/package-2,false,upgrade,1.0.0,1.2.0,
prod,a/package-3,false,downgrade,2.0.0,1.1.1,
prod,a/no-link-2,false,downgrade,2.0.0,1.1.1,
prod,php,false,change,>=7.4.6,^8.0,
dev,a/package-5,false,change,"dev-master 1234567",1.1.1,
dev,a/package-4,false,remove,0.1.1,,"MIT, BSD-3-Clause"
dev,a/no-link-2,false,remove,0.1.1,,MIT

OUTPUT;
        }

        return <<<OUTPUT
section,name,direct,operation,version_base,version_target
prod,a/package-1,false,install,,1.0.0
prod,a/no-link-1,false,install,,1.0.0
prod,a/package-2,false,upgrade,1.0.0,1.2.0
prod,a/package-3,false,downgrade,2.0.0,1.1.1
prod,a/no-link-2,false,downgrade,2.0.0,1.1.1
prod,php,false,change,>=7.4.6,^8.0
dev,a/package-5,false,change,"dev-master 1234567",1.1.1
dev,a/package-4,false,remove,0.1.1,
dev,a/no-link-2,false,remove,0.1.1,

OUTPUT;
    }
}
