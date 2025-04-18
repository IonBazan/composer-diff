<?php

namespace IonBazan\ComposerDiff\Tests\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use IonBazan\ComposerDiff\Formatter\JsonFormatter;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class JsonFormatterTest extends FormatterTest
{
    public function testRenderSingle()
    {
        $sampleData = array(
            new InstallOperation($this->getPackage('a/package-1', '1.0.0')),
            new InstallOperation($this->getPackage('a/no-link-1', '1.0.0')),
            new UpdateOperation($this->getPackage('a/package-2', '1.0.0'), $this->getPackage('a/package-2', '1.2.0')),
            new UpdateOperation($this->getPackage('a/package-3', '2.0.0'), $this->getPackage('a/package-3', '1.1.1')),
            new UpdateOperation($this->getPackage('a/no-link-2', '2.0.0'), $this->getPackage('a/no-link-2', '1.1.1')),
            new UpdateOperation($this->getPackage('a/package-5', 'dev-master', 'dev-master 1234567'), $this->getPackage('a/package-5', '1.1.1')),
            new UninstallOperation($this->getPackage('a/package-4', '0.1.1')),
            new UninstallOperation($this->getPackage('a/no-link-2', '0.1.1')),
        );
        $output = new StreamOutput(fopen('php://memory', 'wb', false));
        $formatter = $this->getFormatter($output);
        $formatter->renderSingle($this->getEntries($sampleData, $this->getGenerators()), 'test', true, false);

        $this->assertSame(self::formatOutput(array(
            'a/package-1' => array(
                    'name' => 'a/package-1',
                    'direct' => false,
                    'operation' => 'install',
                    'version_base' => null,
                    'version_target' => '1.0.0',
                    'compare' => 'https://example.com/r/1.0.0',
                    'link' => 'https://example.com/r/a/package-1',
                ),
            'a/no-link-1' => array(
                    'name' => 'a/no-link-1',
                    'direct' => false,
                    'operation' => 'install',
                    'version_base' => null,
                    'version_target' => '1.0.0',
                    'compare' => null,
                    'link' => null,
                ),
            'a/package-2' => array(
                    'name' => 'a/package-2',
                    'direct' => false,
                    'operation' => 'upgrade',
                    'version_base' => '1.0.0',
                    'version_target' => '1.2.0',
                    'compare' => 'https://example.com/c/1.0.0..1.2.0',
                    'link' => 'https://example.com/r/a/package-2',
                ),
            'a/package-3' => array(
                    'name' => 'a/package-3',
                    'direct' => false,
                    'operation' => 'downgrade',
                    'version_base' => '2.0.0',
                    'version_target' => '1.1.1',
                    'compare' => 'https://example.com/c/2.0.0..1.1.1',
                    'link' => 'https://example.com/r/a/package-3',
                ),
            'a/no-link-2' => array(
                    'name' => 'a/no-link-2',
                    'direct' => false,
                    'operation' => 'remove',
                    'version_base' => '0.1.1',
                    'version_target' => null,
                    'compare' => null,
                    'link' => null,
                ),
            'a/package-5' => array(
                    'name' => 'a/package-5',
                    'direct' => false,
                    'operation' => 'change',
                    'version_base' => 'dev-master 1234567',
                    'version_target' => '1.1.1',
                    'compare' => 'https://example.com/c/dev-master..1.1.1',
                    'link' => 'https://example.com/r/a/package-5',
                ),
            'a/package-4' => array(
                    'name' => 'a/package-4',
                    'direct' => false,
                    'operation' => 'remove',
                    'version_base' => '0.1.1',
                    'version_target' => null,
                    'compare' => 'https://example.com/r/0.1.1',
                    'link' => 'https://example.com/r/a/package-4',
                ),
        )), $this->getDisplay($output));
    }

    protected function getSampleOutput($withUrls, $withLicenses, $decorated)
    {
        $packages = array(
            'packages' => array(
                'a/package-1' => array(
                    'name' => 'a/package-1',
                    'direct' => false,
                    'operation' => 'install',
                    'version_base' => null,
                    'version_target' => '1.0.0',
                    'licenses' => array(),
                    'compare' => 'https://example.com/r/1.0.0',
                    'link' => 'https://example.com/r/a/package-1',
                ),
                'a/no-link-1' => array(
                    'name' => 'a/no-link-1',
                    'direct' => false,
                    'operation' => 'install',
                    'version_base' => null,
                    'version_target' => '1.0.0',
                    'licenses' => array(),
                    'compare' => null,
                    'link' => null,
                ),
                'a/package-2' => array(
                    'name' => 'a/package-2',
                    'direct' => false,
                    'operation' => 'upgrade',
                    'version_base' => '1.0.0',
                    'version_target' => '1.2.0',
                    'licenses' => array(),
                    'compare' => 'https://example.com/c/1.0.0..1.2.0',
                    'link' => 'https://example.com/r/a/package-2',
                ),
                'a/package-3' => array(
                    'name' => 'a/package-3',
                    'direct' => false,
                    'operation' => 'downgrade',
                    'version_base' => '2.0.0',
                    'version_target' => '1.1.1',
                    'licenses' => array(),
                    'compare' => 'https://example.com/c/2.0.0..1.1.1',
                    'link' => 'https://example.com/r/a/package-3',
                ),
                'a/no-link-2' => array(
                    'name' => 'a/no-link-2',
                    'direct' => false,
                    'operation' => 'downgrade',
                    'version_base' => '2.0.0',
                    'version_target' => '1.1.1',
                    'licenses' => array(),
                    'compare' => null,
                    'link' => null,
                ),
                'php' => array(
                    'name' => 'php',
                    'direct' => false,
                    'operation' => 'change',
                    'version_base' => '>=7.4.6',
                    'version_target' => '^8.0',
                    'licenses' => array(),
                    'compare' => null,
                    'link' => null,
                ),
            ),
            'packages-dev' => array(
                'a/package-5' => array(
                    'name' => 'a/package-5',
                    'direct' => false,
                    'operation' => 'change',
                    'version_base' => 'dev-master 1234567',
                    'version_target' => '1.1.1',
                    'licenses' => array(),
                    'compare' => 'https://example.com/c/dev-master..1.1.1',
                    'link' => 'https://example.com/r/a/package-5',
                ),
                'a/package-4' => array(
                    'name' => 'a/package-4',
                    'direct' => false,
                    'operation' => 'remove',
                    'version_base' => '0.1.1',
                    'version_target' => null,
                    'licenses' => array('MIT', 'BSD-3-Clause'),
                    'compare' => 'https://example.com/r/0.1.1',
                    'link' => 'https://example.com/r/a/package-4',
                ),
                'a/no-link-2' => array(
                    'name' => 'a/no-link-2',
                    'direct' => false,
                    'operation' => 'remove',
                    'version_base' => '0.1.1',
                    'version_target' => null,
                    'licenses' => array('MIT'),
                    'compare' => null,
                    'link' => null,
                ),
            ),
        );

        foreach ($packages['packages'] as &$package) {
            if (!$withLicenses) {
                unset($package['licenses']);
            }
            if (!$withUrls) {
                unset($package['compare'], $package['link']);
            }
        }

        foreach ($packages['packages-dev'] as &$package) {
            if (!$withLicenses) {
                unset($package['licenses']);
            }
            if (!$withUrls) {
                unset($package['compare'], $package['link']);
            }
        }

        return self::formatOutput($packages);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormatter(OutputInterface $output)
    {
        return new JsonFormatter($output);
    }

    protected static function getEmptyOutput()
    {
        return self::formatOutput(array('packages' => array(), 'packages-dev' => array()));
    }

    private static function formatOutput(array $result)
    {
        return json_encode($result, 128).PHP_EOL;
    }
}
