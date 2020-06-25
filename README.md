# Composer Diff Plugin

[![Latest version](https://img.shields.io/packagist/v/ion-bazan/composer-diff.svg)](https://packagist.org/packages/ion-bazan/composer-diff)
[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/IonBazan/composer-diff/Tests)](https://github.com/IonBazan/composer-diff/actions)
[![PHP version](https://img.shields.io/packagist/php-v/ion-bazan/composer-diff.svg)](https://packagist.org/packages/ion-bazan/composer-diff)
[![Codecov](https://img.shields.io/codecov/c/gh/IonBazan/composer-diff)](https://codecov.io/gh/IonBazan/composer-diff)
[![Downloads](https://img.shields.io/packagist/dt/ion-bazan/composer-diff.svg)](https://packagist.org/packages/ion-bazan/composer-diff)
[![License](https://img.shields.io/packagist/l/ion-bazan/composer-diff.svg)](https://packagist.org/packages/ion-bazan/composer-diff)

Generates packages changes report in Markdown format by comparing `composer.lock` files. Compares with last-commited changes by default.

![preview](preview.gif)

## Example output

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

# Installation

```shell script
composer global require ion-bazan/composer-diff
```
 
# Usage

```shell script
composer diff # Displays packages changed in current git tree compared with HEAD
``` 

## Options

 - `--base` (`-b`) - path, URL or git ref to original `composer.lock` file
 - `--target` (`-t`) - path, URL or git ref to modified `composer.lock` file
 - `--no-dev` - ignore dev dependencies (`require-dev`)
 - `--no-prod` - ignore prod dependencies (`require`)
 - `--with-platform` (`-p`) - include platform dependencies (PHP, extensions, etc.)
 - `--format` (`-f`) - output format (mdtable, mdlist) - default: `mdtable`
 
## Advanced usage

```shell script
composer diff -b master:composer.lock -t develop:composer.lock -p # Compare master and develop branches, including platform dependencies
composer diff --no-dev # ignore dev dependencies
composer diff -p # include platform dependencies
composer diff -f mdlist # Output as Markdown list instead of table
```


