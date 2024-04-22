<?php

namespace IonBazan\ComposerDiff\Tests\Integration;

use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Package\Package;
use Composer\Plugin\PluginManager;
use IonBazan\ComposerDiff\Command\DiffCommand;
use IonBazan\ComposerDiff\PackageDiff;
use IonBazan\ComposerDiff\Tests\TestCase;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Tester\CommandTester;

class DiffCommandTest extends TestCase
{
    /**
     * @param string $expectedOutput
     *
     * @dataProvider commandArgumentsDataProvider
     */
    public function testCommand($expectedOutput, array $input)
    {
        $command = new DiffCommand(new PackageDiff());
        $command->setApplication($this->getComposerApplication());
        $tester = new CommandTester($command);
        $result = $tester->execute($input);
        $this->assertSame(0, $result);
        $this->assertSame($expectedOutput, $tester->getDisplay());
    }

    /**
     * @param string $expectedOutput
     *
     * @dataProvider commandArgumentsDataProvider
     *
     * @runInSeparateProcess To handle autoloader stuff
     */
    public function testComposerApplication($expectedOutput, array $input)
    {
        $input = array_merge(array('command' => 'diff'), $input);
        $app = $this->getComposerApplication();
        $app->setIO(new NullIO()); // For Composer v1
        $app->setAutoExit(false);
        $plugin = $this->getPluginPackage();
        $config = array('allow-plugins' => array($plugin->getName() => true));
        $composer = Factory::create($app->getIO(), array('config' => $config), true);
        $app->setComposer($composer);
        $pm = new PluginManager($app->getIO(), $composer);
        $composer->setPluginManager($pm);
        $pm->registerPackage($plugin, true);
        $tester = new ApplicationTester($app);
        $result = $tester->run($input, array('verbosity' => Output::VERBOSITY_VERY_VERBOSE));
        $this->assertSame($expectedOutput, $tester->getDisplay());
        $this->assertSame(0, $result);
    }

    public function commandArgumentsDataProvider()
    {
        return array(
            'with platform' => array(
                <<<OUTPUT
| Prod Packages                      | Operation | Base               | Target             |
|------------------------------------|-----------|--------------------|--------------------|
| psr/event-dispatcher               | New       | -                  | 1.0.0              |
| roave/security-advisories          | Changed   | dev-master 3c97c13 | dev-master ac36586 |
| symfony/deprecation-contracts      | New       | -                  | v2.1.2             |
| symfony/event-dispatcher           | Upgraded  | v2.8.52            | v5.1.2             |
| symfony/event-dispatcher-contracts | New       | -                  | v2.1.2             |
| symfony/polyfill-php80             | New       | -                  | v1.17.1            |
| php                                | New       | -                  | >=5.3              |

| Dev Packages                       | Operation  | Base  | Target |
|------------------------------------|------------|-------|--------|
| phpunit/php-code-coverage          | Downgraded | 8.0.2 | 7.0.10 |
| phpunit/php-file-iterator          | Downgraded | 3.0.2 | 2.0.2  |
| phpunit/php-text-template          | Downgraded | 2.0.1 | 1.2.1  |
| phpunit/php-timer                  | Downgraded | 5.0.0 | 2.1.2  |
| phpunit/php-token-stream           | Downgraded | 4.0.2 | 3.1.1  |
| phpunit/phpunit                    | Downgraded | 9.2.5 | 8.5.8  |
| sebastian/code-unit-reverse-lookup | Downgraded | 2.0.1 | 1.0.1  |
| sebastian/comparator               | Downgraded | 4.0.2 | 3.0.2  |
| sebastian/diff                     | Downgraded | 4.0.1 | 3.0.2  |
| sebastian/environment              | Downgraded | 5.1.1 | 4.2.3  |
| sebastian/exporter                 | Downgraded | 4.0.1 | 3.1.2  |
| sebastian/global-state             | Downgraded | 4.0.0 | 3.0.0  |
| sebastian/object-enumerator        | Downgraded | 4.0.1 | 3.0.3  |
| sebastian/object-reflector         | Downgraded | 2.0.1 | 1.1.1  |
| sebastian/recursion-context        | Downgraded | 4.0.1 | 3.0.0  |
| sebastian/resource-operations      | Downgraded | 3.0.1 | 2.0.1  |
| sebastian/type                     | Downgraded | 2.1.0 | 1.1.3  |
| sebastian/version                  | Downgraded | 3.0.0 | 2.0.1  |
| phpunit/php-invoker                | Removed    | 3.0.1 | -      |
| sebastian/code-unit                | Removed    | 1.0.3 | -      |


OUTPUT
            ,
                array(
                    '--base' => __DIR__.'/../fixtures/base/composer.lock',
                    '--target' => __DIR__.'/../fixtures/target/composer.lock',
                    '-p' => null,
                ),
            ),
            'no-dev' => array(
                <<<OUTPUT
| Prod Packages                      | Operation | Base               | Target             |
|------------------------------------|-----------|--------------------|--------------------|
| psr/event-dispatcher               | New       | -                  | 1.0.0              |
| roave/security-advisories          | Changed   | dev-master 3c97c13 | dev-master ac36586 |
| symfony/deprecation-contracts      | New       | -                  | v2.1.2             |
| symfony/event-dispatcher           | Upgraded  | v2.8.52            | v5.1.2             |
| symfony/event-dispatcher-contracts | New       | -                  | v2.1.2             |
| symfony/polyfill-php80             | New       | -                  | v1.17.1            |


OUTPUT
            ,
                array(
                    '--base' => __DIR__.'/../fixtures/base/composer.lock',
                    '--target' => __DIR__.'/../fixtures/target/composer.lock',
                    '--no-dev' => null,
                ),
            ),
            'no-dev with arguments' => array(
                <<<OUTPUT
| Prod Packages                      | Operation | Base               | Target             |
|------------------------------------|-----------|--------------------|--------------------|
| psr/event-dispatcher               | New       | -                  | 1.0.0              |
| roave/security-advisories          | Changed   | dev-master 3c97c13 | dev-master ac36586 |
| symfony/deprecation-contracts      | New       | -                  | v2.1.2             |
| symfony/event-dispatcher           | Upgraded  | v2.8.52            | v5.1.2             |
| symfony/event-dispatcher-contracts | New       | -                  | v2.1.2             |
| symfony/polyfill-php80             | New       | -                  | v1.17.1            |


OUTPUT
            ,
                array(
                    'base' => __DIR__.'/../fixtures/base/composer.lock',
                    'target' => __DIR__.'/../fixtures/target/composer.lock',
                    '--no-dev' => null,
                ),
            ),
            'no-prod' => array(
                <<<OUTPUT
| Dev Packages                       | Operation  | Base  | Target |
|------------------------------------|------------|-------|--------|
| phpunit/php-code-coverage          | Downgraded | 8.0.2 | 7.0.10 |
| phpunit/php-file-iterator          | Downgraded | 3.0.2 | 2.0.2  |
| phpunit/php-text-template          | Downgraded | 2.0.1 | 1.2.1  |
| phpunit/php-timer                  | Downgraded | 5.0.0 | 2.1.2  |
| phpunit/php-token-stream           | Downgraded | 4.0.2 | 3.1.1  |
| phpunit/phpunit                    | Downgraded | 9.2.5 | 8.5.8  |
| sebastian/code-unit-reverse-lookup | Downgraded | 2.0.1 | 1.0.1  |
| sebastian/comparator               | Downgraded | 4.0.2 | 3.0.2  |
| sebastian/diff                     | Downgraded | 4.0.1 | 3.0.2  |
| sebastian/environment              | Downgraded | 5.1.1 | 4.2.3  |
| sebastian/exporter                 | Downgraded | 4.0.1 | 3.1.2  |
| sebastian/global-state             | Downgraded | 4.0.0 | 3.0.0  |
| sebastian/object-enumerator        | Downgraded | 4.0.1 | 3.0.3  |
| sebastian/object-reflector         | Downgraded | 2.0.1 | 1.1.1  |
| sebastian/recursion-context        | Downgraded | 4.0.1 | 3.0.0  |
| sebastian/resource-operations      | Downgraded | 3.0.1 | 2.0.1  |
| sebastian/type                     | Downgraded | 2.1.0 | 1.1.3  |
| sebastian/version                  | Downgraded | 3.0.0 | 2.0.1  |
| phpunit/php-invoker                | Removed    | 3.0.1 | -      |
| sebastian/code-unit                | Removed    | 1.0.3 | -      |


OUTPUT
            ,
                array(
                    '--base' => __DIR__.'/../fixtures/base/composer.lock',
                    '--target' => __DIR__.'/../fixtures/target/composer.lock',
                    '--no-prod' => null,
                ),
            ),
            'reversed, with platform' => array(
                <<<OUTPUT
| Prod Packages                      | Operation  | Base               | Target             |
|------------------------------------|------------|--------------------|--------------------|
| roave/security-advisories          | Changed    | dev-master ac36586 | dev-master 3c97c13 |
| symfony/event-dispatcher           | Downgraded | v5.1.2             | v2.8.52            |
| psr/event-dispatcher               | Removed    | 1.0.0              | -                  |
| symfony/deprecation-contracts      | Removed    | v2.1.2             | -                  |
| symfony/event-dispatcher-contracts | Removed    | v2.1.2             | -                  |
| symfony/polyfill-php80             | Removed    | v1.17.1            | -                  |
| php                                | Removed    | >=5.3              | -                  |

| Dev Packages                       | Operation | Base   | Target |
|------------------------------------|-----------|--------|--------|
| phpunit/php-code-coverage          | Upgraded  | 7.0.10 | 8.0.2  |
| phpunit/php-file-iterator          | Upgraded  | 2.0.2  | 3.0.2  |
| phpunit/php-invoker                | New       | -      | 3.0.1  |
| phpunit/php-text-template          | Upgraded  | 1.2.1  | 2.0.1  |
| phpunit/php-timer                  | Upgraded  | 2.1.2  | 5.0.0  |
| phpunit/php-token-stream           | Upgraded  | 3.1.1  | 4.0.2  |
| phpunit/phpunit                    | Upgraded  | 8.5.8  | 9.2.5  |
| sebastian/code-unit                | New       | -      | 1.0.3  |
| sebastian/code-unit-reverse-lookup | Upgraded  | 1.0.1  | 2.0.1  |
| sebastian/comparator               | Upgraded  | 3.0.2  | 4.0.2  |
| sebastian/diff                     | Upgraded  | 3.0.2  | 4.0.1  |
| sebastian/environment              | Upgraded  | 4.2.3  | 5.1.1  |
| sebastian/exporter                 | Upgraded  | 3.1.2  | 4.0.1  |
| sebastian/global-state             | Upgraded  | 3.0.0  | 4.0.0  |
| sebastian/object-enumerator        | Upgraded  | 3.0.3  | 4.0.1  |
| sebastian/object-reflector         | Upgraded  | 1.1.1  | 2.0.1  |
| sebastian/recursion-context        | Upgraded  | 3.0.0  | 4.0.1  |
| sebastian/resource-operations      | Upgraded  | 2.0.1  | 3.0.1  |
| sebastian/type                     | Upgraded  | 1.1.3  | 2.1.0  |
| sebastian/version                  | Upgraded  | 2.0.1  | 3.0.0  |


OUTPUT
            ,
                array(
                    '--base' => __DIR__.'/../fixtures/target/composer.lock',
                    '--target' => __DIR__.'/../fixtures/base/composer.lock',
                    '-p' => null,
                ),
            ),
            'no changes' => array(
                '',
                array(
                    '--base' => __DIR__.'/../fixtures/base/composer.lock',
                    '--target' => __DIR__.'/../fixtures/base/composer.lock',
                    '-p' => null,
                ),
            ),
        );
    }

    /**
     * @return Package
     */
    private function getPluginPackage()
    {
        $plugin = new Package('test-plugin-package', '1.0', '1.0');
        $plugin->setExtra(array('class' => 'IonBazan\ComposerDiff\Composer\Plugin'));

        return $plugin;
    }
}
