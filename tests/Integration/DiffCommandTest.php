<?php

namespace IonBazan\ComposerDiff\Tests\Integration;

use IonBazan\ComposerDiff\Command\DiffCommand;
use IonBazan\ComposerDiff\PackageDiff;
use IonBazan\ComposerDiff\Tests\TestCase;
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
        $tester = new CommandTester(new DiffCommand(new PackageDiff()));
        $result = $tester->execute($input);
        $this->assertSame(0, $result);
        $this->assertSame($expectedOutput, $tester->getDisplay());
    }

    public function commandArgumentsDataProvider()
    {
        return array(
            'with platform' => array(
                <<<OUTPUT
| Prod Packages                      | Base    | Target  |
|------------------------------------|---------|---------|
| psr/event-dispatcher               | New     | 1.0.0   |
| symfony/deprecation-contracts      | New     | v2.1.2  |
| symfony/event-dispatcher           | v2.8.52 | v5.1.2  |
| symfony/event-dispatcher-contracts | New     | v2.1.2  |
| symfony/polyfill-php80             | New     | v1.17.1 |
| php                                | New     | >=5.3   |

| Dev Packages                       | Base  | Target  |
|------------------------------------|-------|---------|
| phpunit/php-code-coverage          | 8.0.2 | 7.0.10  |
| phpunit/php-file-iterator          | 3.0.2 | 2.0.2   |
| phpunit/php-text-template          | 2.0.1 | 1.2.1   |
| phpunit/php-timer                  | 5.0.0 | 2.1.2   |
| phpunit/php-token-stream           | 4.0.2 | 3.1.1   |
| phpunit/phpunit                    | 9.2.5 | 8.5.8   |
| sebastian/code-unit-reverse-lookup | 2.0.1 | 1.0.1   |
| sebastian/comparator               | 4.0.2 | 3.0.2   |
| sebastian/diff                     | 4.0.1 | 3.0.2   |
| sebastian/environment              | 5.1.1 | 4.2.3   |
| sebastian/exporter                 | 4.0.1 | 3.1.2   |
| sebastian/global-state             | 4.0.0 | 3.0.0   |
| sebastian/object-enumerator        | 4.0.1 | 3.0.3   |
| sebastian/object-reflector         | 2.0.1 | 1.1.1   |
| sebastian/recursion-context        | 4.0.1 | 3.0.0   |
| sebastian/resource-operations      | 3.0.1 | 2.0.1   |
| sebastian/type                     | 2.1.0 | 1.1.3   |
| sebastian/version                  | 3.0.0 | 2.0.1   |
| phpunit/php-invoker                | 3.0.1 | Removed |
| sebastian/code-unit                | 1.0.3 | Removed |


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
| Prod Packages                      | Base    | Target  |
|------------------------------------|---------|---------|
| psr/event-dispatcher               | New     | 1.0.0   |
| symfony/deprecation-contracts      | New     | v2.1.2  |
| symfony/event-dispatcher           | v2.8.52 | v5.1.2  |
| symfony/event-dispatcher-contracts | New     | v2.1.2  |
| symfony/polyfill-php80             | New     | v1.17.1 |


OUTPUT
            ,
                array(
                    '--base' => __DIR__.'/../fixtures/base/composer.lock',
                    '--target' => __DIR__.'/../fixtures/target/composer.lock',
                    '--no-dev' => null,
                ),
            ),
            'no-prod' => array(
                <<<OUTPUT
| Dev Packages                       | Base  | Target  |
|------------------------------------|-------|---------|
| phpunit/php-code-coverage          | 8.0.2 | 7.0.10  |
| phpunit/php-file-iterator          | 3.0.2 | 2.0.2   |
| phpunit/php-text-template          | 2.0.1 | 1.2.1   |
| phpunit/php-timer                  | 5.0.0 | 2.1.2   |
| phpunit/php-token-stream           | 4.0.2 | 3.1.1   |
| phpunit/phpunit                    | 9.2.5 | 8.5.8   |
| sebastian/code-unit-reverse-lookup | 2.0.1 | 1.0.1   |
| sebastian/comparator               | 4.0.2 | 3.0.2   |
| sebastian/diff                     | 4.0.1 | 3.0.2   |
| sebastian/environment              | 5.1.1 | 4.2.3   |
| sebastian/exporter                 | 4.0.1 | 3.1.2   |
| sebastian/global-state             | 4.0.0 | 3.0.0   |
| sebastian/object-enumerator        | 4.0.1 | 3.0.3   |
| sebastian/object-reflector         | 2.0.1 | 1.1.1   |
| sebastian/recursion-context        | 4.0.1 | 3.0.0   |
| sebastian/resource-operations      | 3.0.1 | 2.0.1   |
| sebastian/type                     | 2.1.0 | 1.1.3   |
| sebastian/version                  | 3.0.0 | 2.0.1   |
| phpunit/php-invoker                | 3.0.1 | Removed |
| sebastian/code-unit                | 1.0.3 | Removed |


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
| Prod Packages                      | Base    | Target  |
|------------------------------------|---------|---------|
| symfony/event-dispatcher           | v5.1.2  | v2.8.52 |
| psr/event-dispatcher               | 1.0.0   | Removed |
| symfony/deprecation-contracts      | v2.1.2  | Removed |
| symfony/event-dispatcher-contracts | v2.1.2  | Removed |
| symfony/polyfill-php80             | v1.17.1 | Removed |
| php                                | >=5.3   | Removed |

| Dev Packages                       | Base   | Target |
|------------------------------------|--------|--------|
| phpunit/php-code-coverage          | 7.0.10 | 8.0.2  |
| phpunit/php-file-iterator          | 2.0.2  | 3.0.2  |
| phpunit/php-invoker                | New    | 3.0.1  |
| phpunit/php-text-template          | 1.2.1  | 2.0.1  |
| phpunit/php-timer                  | 2.1.2  | 5.0.0  |
| phpunit/php-token-stream           | 3.1.1  | 4.0.2  |
| phpunit/phpunit                    | 8.5.8  | 9.2.5  |
| sebastian/code-unit                | New    | 1.0.3  |
| sebastian/code-unit-reverse-lookup | 1.0.1  | 2.0.1  |
| sebastian/comparator               | 3.0.2  | 4.0.2  |
| sebastian/diff                     | 3.0.2  | 4.0.1  |
| sebastian/environment              | 4.2.3  | 5.1.1  |
| sebastian/exporter                 | 3.1.2  | 4.0.1  |
| sebastian/global-state             | 3.0.0  | 4.0.0  |
| sebastian/object-enumerator        | 3.0.3  | 4.0.1  |
| sebastian/object-reflector         | 1.1.1  | 2.0.1  |
| sebastian/recursion-context        | 3.0.0  | 4.0.1  |
| sebastian/resource-operations      | 2.0.1  | 3.0.1  |
| sebastian/type                     | 1.1.3  | 2.1.0  |
| sebastian/version                  | 2.0.1  | 3.0.0  |


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
}
