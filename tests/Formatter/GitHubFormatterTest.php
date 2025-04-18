<?php

namespace IonBazan\ComposerDiff\Tests\Formatter;

use IonBazan\ComposerDiff\Formatter\GitHubFormatter;
use Symfony\Component\Console\Output\OutputInterface;

class GitHubFormatterTest extends FormatterTest
{
    protected function getSampleOutput($withUrls, $withLicenses, $decorated)
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

    /**
     * {@inheritdoc}
     */
    protected function getFormatter(OutputInterface $output)
    {
        return new GitHubFormatter($output);
    }
}
