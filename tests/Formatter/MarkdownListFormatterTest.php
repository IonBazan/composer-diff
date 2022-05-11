<?php

namespace IonBazan\ComposerDiff\Tests\Formatter;

use IonBazan\ComposerDiff\Formatter\MarkdownListFormatter;
use IonBazan\ComposerDiff\Url\GeneratorContainer;
use Symfony\Component\Console\Output\OutputInterface;

class MarkdownListFormatterTest extends FormatterTest
{
    protected function getSampleOutput($withUrls, $decorated)
    {
        if ($withUrls) {
            if ($decorated) {
                return <<<OUTPUT
Prod Packages
=============

 - Install [32m[a/package-1](https://example.com/r/a/package-1)[39m ([33m1.0.0[39m) [Compare](https://example.com/r/1.0.0)
 - Install [32ma/no-link-1[39m ([33m1.0.0[39m) 
 - Upgrade [32m[a/package-2](https://example.com/r/a/package-2)[39m ([33m1.0.0[39m => [33m1.2.0[39m) [Compare](https://example.com/c/1.0.0..1.2.0)
 - Downgrade [32m[a/package-3](https://example.com/r/a/package-3)[39m ([33m2.0.0[39m => [33m1.1.1[39m) [Compare](https://example.com/c/2.0.0..1.1.1)
 - Downgrade [32ma/no-link-2[39m ([33m2.0.0[39m => [33m1.1.1[39m) 
 - Change [32mphp[39m ([33m>=7.4.6[39m => [33m^8.0[39m) 

Dev Packages
============

 - Change [32m[a/package-5](https://example.com/r/a/package-5)[39m ([33mdev-master 1234567[39m => [33m1.1.1[39m) [Compare](https://example.com/c/dev-master..1.1.1)
 - Uninstall [32m[a/package-4](https://example.com/r/a/package-4)[39m ([33m0.1.1[39m) [Compare](https://example.com/r/0.1.1)
 - Uninstall [32ma/no-link-2[39m ([33m0.1.1[39m) 


OUTPUT;
            }

            return <<<OUTPUT
Prod Packages
=============

 - Install [a/package-1](https://example.com/r/a/package-1) (1.0.0) [Compare](https://example.com/r/1.0.0)
 - Install a/no-link-1 (1.0.0) 
 - Upgrade [a/package-2](https://example.com/r/a/package-2) (1.0.0 => 1.2.0) [Compare](https://example.com/c/1.0.0..1.2.0)
 - Downgrade [a/package-3](https://example.com/r/a/package-3) (2.0.0 => 1.1.1) [Compare](https://example.com/c/2.0.0..1.1.1)
 - Downgrade a/no-link-2 (2.0.0 => 1.1.1) 
 - Change php (>=7.4.6 => ^8.0) 

Dev Packages
============

 - Change [a/package-5](https://example.com/r/a/package-5) (dev-master 1234567 => 1.1.1) [Compare](https://example.com/c/dev-master..1.1.1)
 - Uninstall [a/package-4](https://example.com/r/a/package-4) (0.1.1) [Compare](https://example.com/r/0.1.1)
 - Uninstall a/no-link-2 (0.1.1) 


OUTPUT;
        }

        if ($decorated) {
            return <<<OUTPUT
Prod Packages
=============

 - Install [32ma/package-1[39m ([33m1.0.0[39m)
 - Install [32ma/no-link-1[39m ([33m1.0.0[39m)
 - Upgrade [32ma/package-2[39m ([33m1.0.0[39m => [33m1.2.0[39m)
 - Downgrade [32ma/package-3[39m ([33m2.0.0[39m => [33m1.1.1[39m)
 - Downgrade [32ma/no-link-2[39m ([33m2.0.0[39m => [33m1.1.1[39m)
 - Change [32mphp[39m ([33m>=7.4.6[39m => [33m^8.0[39m)

Dev Packages
============

 - Change [32ma/package-5[39m ([33mdev-master 1234567[39m => [33m1.1.1[39m)
 - Uninstall [32ma/package-4[39m ([33m0.1.1[39m)
 - Uninstall [32ma/no-link-2[39m ([33m0.1.1[39m)


OUTPUT;
        }

        return <<<OUTPUT
Prod Packages
=============

 - Install a/package-1 (1.0.0)
 - Install a/no-link-1 (1.0.0)
 - Upgrade a/package-2 (1.0.0 => 1.2.0)
 - Downgrade a/package-3 (2.0.0 => 1.1.1)
 - Downgrade a/no-link-2 (2.0.0 => 1.1.1)
 - Change php (>=7.4.6 => ^8.0)

Dev Packages
============

 - Change a/package-5 (dev-master 1234567 => 1.1.1)
 - Uninstall a/package-4 (0.1.1)
 - Uninstall a/no-link-2 (0.1.1)


OUTPUT;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormatter(OutputInterface $output, GeneratorContainer $generators)
    {
        return new MarkdownListFormatter($output, $generators);
    }
}
