# Composer Diff Plugin

Generates packages changes report in Markdown format by comparing `composer.lock` files.

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
 
## Advanced usage

```shell script
composer diff -b master:composer.lock -t develop:composer.lock -p # Compare master and develop branches, including platform dependencies
composer diff --no-dev # ignore dev dependencies
composer diff -p # include platform dependencies
```


