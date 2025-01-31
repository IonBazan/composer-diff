<?php

namespace IonBazan\ComposerDiff\Tests\Formatter;

use IonBazan\ComposerDiff\Formatter\MarkdownTableFormatter;
use IonBazan\ComposerDiff\Url\GeneratorContainer;
use Symfony\Component\Console\Output\OutputInterface;

class MarkdownTableFormatterTest extends FormatterTest
{
    protected function getSampleOutput($withUrls, $withLicenses, $decorated)
    {
        if ($withLicenses) {
            $prodLicenseHeader = ' License |';
            $prodLicenseSeparator = '---------|';
            $prodNullLicense = '         |';

            $devLicenseHeader = ' License           |';
            $devLicenseSeparator = '-------------------|';
            $devNullLicense = '                   |';
            $package4License = ' MIT, BSD-3-Clause |';
            $noLink2License = ' MIT               |';
        } else {
            $prodLicenseHeader = '';
            $prodLicenseSeparator = '';
            $prodNullLicense = '';
            $devLicenseHeader = '';
            $devLicenseSeparator = '';
            $devNullLicense = '';
            $package4License = '';
            $noLink2License = '';
        }

        if ($withUrls) {
            if ($decorated) {
                if ($this->supportsLinks()) {
                    return <<<OUTPUT
| Prod Packages                                    | Operation  | Base    | Target | Link                                          |{$prodLicenseHeader}
|--------------------------------------------------|------------|---------|--------|-----------------------------------------------|{$prodLicenseSeparator}
| []8;;https://example.com/r/a/package-1\\a/package-1]8;;\\](https://example.com/r/a/package-1) | [32mNew[39m        | -       | 1.0.0  | [Compare](https://example.com/r/1.0.0)        |{$prodNullLicense}
| a/no-link-1                                      | [32mNew[39m        | -       | 1.0.0  |                                               |{$prodNullLicense}
| []8;;https://example.com/r/a/package-2\\a/package-2]8;;\\](https://example.com/r/a/package-2) | [36mUpgraded[39m   | 1.0.0   | 1.2.0  | [Compare](https://example.com/c/1.0.0..1.2.0) |{$prodNullLicense}
| []8;;https://example.com/r/a/package-3\\a/package-3]8;;\\](https://example.com/r/a/package-3) | [33mDowngraded[39m | 2.0.0   | 1.1.1  | [Compare](https://example.com/c/2.0.0..1.1.1) |{$prodNullLicense}
| a/no-link-2                                      | [33mDowngraded[39m | 2.0.0   | 1.1.1  |                                               |{$prodNullLicense}
| php                                              | [35mChanged[39m    | >=7.4.6 | ^8.0   |                                               |{$prodNullLicense}

| Dev Packages                                     | Operation | Base               | Target | Link                                               |{$devLicenseHeader}
|--------------------------------------------------|-----------|--------------------|--------|----------------------------------------------------|{$devLicenseSeparator}
| []8;;https://example.com/r/a/package-5\\a/package-5]8;;\\](https://example.com/r/a/package-5) | [35mChanged[39m   | dev-master 1234567 | 1.1.1  | [Compare](https://example.com/c/dev-master..1.1.1) |{$devNullLicense}
| []8;;https://example.com/r/a/package-4\\a/package-4]8;;\\](https://example.com/r/a/package-4) | [31mRemoved[39m   | 0.1.1              | -      | [Compare](https://example.com/r/0.1.1)             |{$package4License}
| a/no-link-2                                      | [31mRemoved[39m   | 0.1.1              | -      |                                                    |{$noLink2License}


OUTPUT;
                }

                return <<<OUTPUT
| Prod Packages                                    | Operation  | Base    | Target | Link                                          |{$prodLicenseHeader}
|--------------------------------------------------|------------|---------|--------|-----------------------------------------------|{$prodLicenseSeparator}
| [a/package-1](https://example.com/r/a/package-1) | [32mNew[39m        | -       | 1.0.0  | [Compare](https://example.com/r/1.0.0)        |{$prodNullLicense}
| a/no-link-1                                      | [32mNew[39m        | -       | 1.0.0  |                                               |{$prodNullLicense}
| [a/package-2](https://example.com/r/a/package-2) | [36mUpgraded[39m   | 1.0.0   | 1.2.0  | [Compare](https://example.com/c/1.0.0..1.2.0) |{$prodNullLicense}
| [a/package-3](https://example.com/r/a/package-3) | [33mDowngraded[39m | 2.0.0   | 1.1.1  | [Compare](https://example.com/c/2.0.0..1.1.1) |{$prodNullLicense}
| a/no-link-2                                      | [33mDowngraded[39m | 2.0.0   | 1.1.1  |                                               |{$prodNullLicense}
| php                                              | [35mChanged[39m    | >=7.4.6 | ^8.0   |                                               |{$prodNullLicense}

| Dev Packages                                     | Operation | Base               | Target | Link                                               |{$devLicenseHeader}
|--------------------------------------------------|-----------|--------------------|--------|----------------------------------------------------|{$devLicenseSeparator}
| [a/package-5](https://example.com/r/a/package-5) | [35mChanged[39m   | dev-master 1234567 | 1.1.1  | [Compare](https://example.com/c/dev-master..1.1.1) |{$devNullLicense}
| [a/package-4](https://example.com/r/a/package-4) | [31mRemoved[39m   | 0.1.1              | -      | [Compare](https://example.com/r/0.1.1)             |{$package4License}
| a/no-link-2                                      | [31mRemoved[39m   | 0.1.1              | -      |                                                    |{$noLink2License}


OUTPUT;
            }

            return <<<OUTPUT
| Prod Packages                                    | Operation  | Base    | Target | Link                                          |{$prodLicenseHeader}
|--------------------------------------------------|------------|---------|--------|-----------------------------------------------|{$prodLicenseSeparator}
| [a/package-1](https://example.com/r/a/package-1) | New        | -       | 1.0.0  | [Compare](https://example.com/r/1.0.0)        |{$prodNullLicense}
| a/no-link-1                                      | New        | -       | 1.0.0  |                                               |{$prodNullLicense}
| [a/package-2](https://example.com/r/a/package-2) | Upgraded   | 1.0.0   | 1.2.0  | [Compare](https://example.com/c/1.0.0..1.2.0) |{$prodNullLicense}
| [a/package-3](https://example.com/r/a/package-3) | Downgraded | 2.0.0   | 1.1.1  | [Compare](https://example.com/c/2.0.0..1.1.1) |{$prodNullLicense}
| a/no-link-2                                      | Downgraded | 2.0.0   | 1.1.1  |                                               |{$prodNullLicense}
| php                                              | Changed    | >=7.4.6 | ^8.0   |                                               |{$prodNullLicense}

| Dev Packages                                     | Operation | Base               | Target | Link                                               |{$devLicenseHeader}
|--------------------------------------------------|-----------|--------------------|--------|----------------------------------------------------|{$devLicenseSeparator}
| [a/package-5](https://example.com/r/a/package-5) | Changed   | dev-master 1234567 | 1.1.1  | [Compare](https://example.com/c/dev-master..1.1.1) |{$devNullLicense}
| [a/package-4](https://example.com/r/a/package-4) | Removed   | 0.1.1              | -      | [Compare](https://example.com/r/0.1.1)             |{$package4License}
| a/no-link-2                                      | Removed   | 0.1.1              | -      |                                                    |{$noLink2License}


OUTPUT;
        }

        if ($decorated) {
            if ($this->supportsLinks()) {
                return <<<OUTPUT
| Prod Packages | Operation  | Base    | Target |{$prodLicenseHeader}
|---------------|------------|---------|--------|{$prodLicenseSeparator}
| ]8;;https://example.com/r/a/package-1\a/package-1]8;;\   | [32mNew[39m        | -       | 1.0.0  |{$prodNullLicense}
| a/no-link-1   | [32mNew[39m        | -       | 1.0.0  |{$prodNullLicense}
| ]8;;https://example.com/r/a/package-2\a/package-2]8;;\   | [36mUpgraded[39m   | 1.0.0   | 1.2.0  |{$prodNullLicense}
| ]8;;https://example.com/r/a/package-3\a/package-3]8;;\   | [33mDowngraded[39m | 2.0.0   | 1.1.1  |{$prodNullLicense}
| a/no-link-2   | [33mDowngraded[39m | 2.0.0   | 1.1.1  |{$prodNullLicense}
| php           | [35mChanged[39m    | >=7.4.6 | ^8.0   |{$prodNullLicense}

| Dev Packages | Operation | Base               | Target |{$devLicenseHeader}
|--------------|-----------|--------------------|--------|{$devLicenseSeparator}
| ]8;;https://example.com/r/a/package-5\a/package-5]8;;\  | [35mChanged[39m   | dev-master 1234567 | 1.1.1  |{$devNullLicense}
| ]8;;https://example.com/r/a/package-4\a/package-4]8;;\  | [31mRemoved[39m   | 0.1.1              | -      |{$package4License}
| a/no-link-2  | [31mRemoved[39m   | 0.1.1              | -      |{$noLink2License}


OUTPUT;
            }

            return <<<OUTPUT
| Prod Packages | Operation  | Base    | Target |{$prodLicenseHeader}
|---------------|------------|---------|--------|{$prodLicenseSeparator}
| a/package-1   | [32mNew[39m        | -       | 1.0.0  |{$prodNullLicense}
| a/no-link-1   | [32mNew[39m        | -       | 1.0.0  |{$prodNullLicense}
| a/package-2   | [36mUpgraded[39m   | 1.0.0   | 1.2.0  |{$prodNullLicense}
| a/package-3   | [33mDowngraded[39m | 2.0.0   | 1.1.1  |{$prodNullLicense}
| a/no-link-2   | [33mDowngraded[39m | 2.0.0   | 1.1.1  |{$prodNullLicense}
| php           | [35mChanged[39m    | >=7.4.6 | ^8.0   |{$prodNullLicense}

| Dev Packages | Operation | Base               | Target |{$devLicenseHeader}
|--------------|-----------|--------------------|--------|{$devLicenseSeparator}
| a/package-5  | [35mChanged[39m   | dev-master 1234567 | 1.1.1  |{$devNullLicense}
| a/package-4  | [31mRemoved[39m   | 0.1.1              | -      |{$package4License}
| a/no-link-2  | [31mRemoved[39m   | 0.1.1              | -      |{$noLink2License}


OUTPUT;
        }

        return <<<OUTPUT
| Prod Packages | Operation  | Base    | Target |{$prodLicenseHeader}
|---------------|------------|---------|--------|{$prodLicenseSeparator}
| a/package-1   | New        | -       | 1.0.0  |{$prodNullLicense}
| a/no-link-1   | New        | -       | 1.0.0  |{$prodNullLicense}
| a/package-2   | Upgraded   | 1.0.0   | 1.2.0  |{$prodNullLicense}
| a/package-3   | Downgraded | 2.0.0   | 1.1.1  |{$prodNullLicense}
| a/no-link-2   | Downgraded | 2.0.0   | 1.1.1  |{$prodNullLicense}
| php           | Changed    | >=7.4.6 | ^8.0   |{$prodNullLicense}

| Dev Packages | Operation | Base               | Target |{$devLicenseHeader}
|--------------|-----------|--------------------|--------|{$devLicenseSeparator}
| a/package-5  | Changed   | dev-master 1234567 | 1.1.1  |{$devNullLicense}
| a/package-4  | Removed   | 0.1.1              | -      |{$package4License}
| a/no-link-2  | Removed   | 0.1.1              | -      |{$noLink2License}


OUTPUT;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormatter(OutputInterface $output, GeneratorContainer $generators)
    {
        return new MarkdownTableFormatter($output, $generators);
    }
}
