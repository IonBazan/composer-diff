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
            'section,name,direct,operation,version_base,version_target,licenses,effective'.PHP_EOL.
            "test,a/package-1,true,install,,1.0.0,\"Quoted \"\"license\"\", Multi\nline, Carriage\rreturn, <info>Tagged</info>\",false".PHP_EOL,
            $this->getDisplay($output)
        );
    }

    protected function getEffectivePlatformOutput(): string
    {
        return <<<OUTPUT
section,name,direct,operation,version_base,version_target,effective
prod,ext-intl,false,install,,*,true
prod,php,false,change,>=7.2,>=8.0,true
prod,ext-xdebug,false,remove,*,,true

OUTPUT;
    }

    protected function getFormatter(OutputInterface $output): Formatter
    {
        return new CsvFormatter($output);
    }

    protected static function getEmptyOutput(): string
    {
        return 'section,name,direct,operation,version_base,version_target,compare,link,effective'.PHP_EOL;
    }

    protected function getSampleOutput(bool $withUrls, bool $withLicenses, bool $decorated): string
    {
        if ($withUrls && $withLicenses) {
            return <<<OUTPUT
section,name,direct,operation,version_base,version_target,licenses,compare,link,effective
prod,a/package-1,false,install,,1.0.0,,https://example.com/r/1.0.0,https://example.com/r/a/package-1,false
prod,a/no-link-1,false,install,,1.0.0,,,,false
prod,a/package-2,false,upgrade,1.0.0,1.2.0,,https://example.com/c/1.0.0..1.2.0,https://example.com/r/a/package-2,false
prod,a/package-3,false,downgrade,2.0.0,1.1.1,,https://example.com/c/2.0.0..1.1.1,https://example.com/r/a/package-3,false
prod,a/no-link-2,false,downgrade,2.0.0,1.1.1,,,,false
prod,php,false,change,>=7.4.6,^8.0,,,,false
dev,a/package-5,false,change,"dev-master 1234567",1.1.1,,https://example.com/c/dev-master..1.1.1,https://example.com/r/a/package-5,false
dev,a/package-4,false,remove,0.1.1,,"MIT, BSD-3-Clause",https://example.com/r/0.1.1,https://example.com/r/a/package-4,false
dev,a/no-link-2,false,remove,0.1.1,,MIT,,,false

OUTPUT;
        }

        if ($withUrls) {
            return <<<OUTPUT
section,name,direct,operation,version_base,version_target,compare,link,effective
prod,a/package-1,false,install,,1.0.0,https://example.com/r/1.0.0,https://example.com/r/a/package-1,false
prod,a/no-link-1,false,install,,1.0.0,,,false
prod,a/package-2,false,upgrade,1.0.0,1.2.0,https://example.com/c/1.0.0..1.2.0,https://example.com/r/a/package-2,false
prod,a/package-3,false,downgrade,2.0.0,1.1.1,https://example.com/c/2.0.0..1.1.1,https://example.com/r/a/package-3,false
prod,a/no-link-2,false,downgrade,2.0.0,1.1.1,,,false
prod,php,false,change,>=7.4.6,^8.0,,,false
dev,a/package-5,false,change,"dev-master 1234567",1.1.1,https://example.com/c/dev-master..1.1.1,https://example.com/r/a/package-5,false
dev,a/package-4,false,remove,0.1.1,,https://example.com/r/0.1.1,https://example.com/r/a/package-4,false
dev,a/no-link-2,false,remove,0.1.1,,,,false

OUTPUT;
        }

        if ($withLicenses) {
            return <<<OUTPUT
section,name,direct,operation,version_base,version_target,licenses,effective
prod,a/package-1,false,install,,1.0.0,,false
prod,a/no-link-1,false,install,,1.0.0,,false
prod,a/package-2,false,upgrade,1.0.0,1.2.0,,false
prod,a/package-3,false,downgrade,2.0.0,1.1.1,,false
prod,a/no-link-2,false,downgrade,2.0.0,1.1.1,,false
prod,php,false,change,>=7.4.6,^8.0,,false
dev,a/package-5,false,change,"dev-master 1234567",1.1.1,,false
dev,a/package-4,false,remove,0.1.1,,"MIT, BSD-3-Clause",false
dev,a/no-link-2,false,remove,0.1.1,,MIT,false

OUTPUT;
        }

        return <<<OUTPUT
section,name,direct,operation,version_base,version_target,effective
prod,a/package-1,false,install,,1.0.0,false
prod,a/no-link-1,false,install,,1.0.0,false
prod,a/package-2,false,upgrade,1.0.0,1.2.0,false
prod,a/package-3,false,downgrade,2.0.0,1.1.1,false
prod,a/no-link-2,false,downgrade,2.0.0,1.1.1,false
prod,php,false,change,>=7.4.6,^8.0,false
dev,a/package-5,false,change,"dev-master 1234567",1.1.1,false
dev,a/package-4,false,remove,0.1.1,,false
dev,a/no-link-2,false,remove,0.1.1,,false

OUTPUT;
    }
}
