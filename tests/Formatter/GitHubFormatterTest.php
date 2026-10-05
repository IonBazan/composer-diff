<?php

namespace IonBazan\ComposerDiff\Tests\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Formatter\Formatter;
use IonBazan\ComposerDiff\Formatter\GitHubFormatter;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class GitHubFormatterTest extends FormatterTest
{
    protected function getSampleOutput(bool $withUrls, bool $withLicenses, bool $decorated): string
    {
        if ($withLicenses) {
            $package4License = ' (License: MIT, BSD-3-Clause)';
            $noLink2License = ' (License: MIT)';
        } else {
            $package4License = '';
            $noLink2License = '';
        }

        if ($withUrls) {
            return <<<OUTPUT
::notice title=Prod Packages:: - Install a/package-1 (1.0.0) https://example.com/r/1.0.0%0A - Install a/no-link-1 (1.0.0)%0A - Upgrade a/package-2 (1.0.0 => 1.2.0) https://example.com/c/1.0.0..1.2.0%0A - Downgrade a/package-3 (2.0.0 => 1.1.1) https://example.com/c/2.0.0..1.1.1%0A - Downgrade a/no-link-2 (2.0.0 => 1.1.1)%0A - Change php (>=7.4.6 => ^8.0)
::notice title=Dev Packages:: - Change a/package-5 (dev-master 1234567 => 1.1.1) https://example.com/c/dev-master..1.1.1%0A - Uninstall a/package-4 (0.1.1) https://example.com/r/0.1.1{$package4License}%0A - Uninstall a/no-link-2 (0.1.1){$noLink2License}

OUTPUT;
        }

        return <<<OUTPUT
::notice title=Prod Packages:: - Install a/package-1 (1.0.0)%0A - Install a/no-link-1 (1.0.0)%0A - Upgrade a/package-2 (1.0.0 => 1.2.0)%0A - Downgrade a/package-3 (2.0.0 => 1.1.1)%0A - Downgrade a/no-link-2 (2.0.0 => 1.1.1)%0A - Change php (>=7.4.6 => ^8.0)
::notice title=Dev Packages:: - Change a/package-5 (dev-master 1234567 => 1.1.1)%0A - Uninstall a/package-4 (0.1.1){$package4License}%0A - Uninstall a/no-link-2 (0.1.1){$noLink2License}

OUTPUT;
    }

    public function testItEscapesWorkflowCommandValues(): void
    {
        $output = new BufferedOutput();
        $this->getFormatter($output)->renderSingle(new DiffEntries([
            new DiffEntry(new InstallOperation($this->getPackage('a/package-1', "1.0%0D\r2"))),
            new DiffEntry(new InstallOperation($this->getPackage('a/package-2', '1.0'))),
        ]), 'Title: a, b%', false, false);

        $this->assertSame(
            '::notice title=Title%3A a%2C b%25:: - Install a/package-1 (1.0%250D%0D2)%0A - Install a/package-2 (1.0)'.PHP_EOL,
            $output->fetch()
        );
    }

    protected function getEffectivePlatformOutput(): string
    {
        return <<<OUTPUT
::notice title=Prod Packages:: - Install ext-intl (effective) (*)%0A - Change php (effective) (>=7.2 => >=8.0)%0A - Uninstall ext-xdebug (effective) (*)

OUTPUT;
    }

    protected function getFormatter(OutputInterface $output): Formatter
    {
        return new GitHubFormatter($output);
    }
}
