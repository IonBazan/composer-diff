# Output formatters

There are currently six built-in output formats available:

- `mdtable` - Markdown table (default)
- `mdlist` - Markdown list
- `json` - JSON
- `csv` - CSV
- `github` - GitHub Annotations
- `pr` - Collapsible GitHub PR description

You can select the output format using the `--format` (`-f`) option.
Other packages can add formats, see [Extensions](extensions.md#custom-formatters).

```shell script
composer diff --format mdlist
composer diff -f json
```

## Markdown table (mdtable)

This is the default output format. It will display the changes in a table format.

Example output:

```
| Prod Packages                      | Operation | Base               | Target             |
|------------------------------------|-----------|--------------------|--------------------|
| psr/event-dispatcher               | New       | -                  | 1.0.0              |
| roave/security-advisories          | Changed   | dev-master 3c97c13 | dev-master ac36586 |
| symfony/deprecation-contracts      | New       | -                  | v2.1.2             |
| symfony/event-dispatcher           | Upgraded  | v2.8.52            | v5.1.2             |
| symfony/event-dispatcher-contracts | New       | -                  | v2.1.2             |
| symfony/polyfill-php80             | New       | -                  | v1.17.1            |

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
```

Rendered output:

| Prod Packages                      | Operation | Base               | Target             |
|------------------------------------|-----------|--------------------|--------------------|
| psr/event-dispatcher               | New       | -                  | 1.0.0              |
| roave/security-advisories          | Changed   | dev-master 3c97c13 | dev-master ac36586 |
| symfony/deprecation-contracts      | New       | -                  | v2.1.2             |
| symfony/event-dispatcher           | Upgraded  | v2.8.52            | v5.1.2             |
| symfony/event-dispatcher-contracts | New       | -                  | v2.1.2             |
| symfony/polyfill-php80             | New       | -                  | v1.17.1            |

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

## Markdown list (mdlist)

This format will display the changes in a markdown list format.

Example output:

```
Prod Packages
=============

 - Install psr/event-dispatcher (1.0.0)
 - Change roave/security-advisories (dev-master 3c97c13 => dev-master ac36586)
 - Install symfony/deprecation-contracts (v2.1.2)
 - Upgrade symfony/event-dispatcher (v2.8.52 => v5.1.2)
 - Install symfony/event-dispatcher-contracts (v2.1.2)
 - Install symfony/polyfill-php80 (v1.17.1)

Dev Packages
============

 - Downgrade phpunit/php-code-coverage (8.0.2 => 7.0.10)
 - Downgrade phpunit/php-file-iterator (3.0.2 => 2.0.2)
 - Downgrade phpunit/php-text-template (2.0.1 => 1.2.1)
 - Downgrade phpunit/php-timer (5.0.0 => 2.1.2)
 - Downgrade phpunit/php-token-stream (4.0.2 => 3.1.1)
 - Downgrade phpunit/phpunit (9.2.5 => 8.5.8)
 - Downgrade sebastian/code-unit-reverse-lookup (2.0.1 => 1.0.1)
 - Downgrade sebastian/comparator (4.0.2 => 3.0.2)
 - Downgrade sebastian/diff (4.0.1 => 3.0.2)
 - Downgrade sebastian/environment (5.1.1 => 4.2.3)
 - Downgrade sebastian/exporter (4.0.1 => 3.1.2)
 - Downgrade sebastian/global-state (4.0.0 => 3.0.0)
 - Downgrade sebastian/object-enumerator (4.0.1 => 3.0.3)
 - Downgrade sebastian/object-reflector (2.0.1 => 1.1.1)
 - Downgrade sebastian/recursion-context (4.0.1 => 3.0.0)
 - Downgrade sebastian/resource-operations (3.0.1 => 2.0.1)
 - Downgrade sebastian/type (2.1.0 => 1.1.3)
 - Downgrade sebastian/version (3.0.0 => 2.0.1)
 - Uninstall phpunit/php-invoker (3.0.1)
 - Uninstall sebastian/code-unit (1.0.3)
```

Rendered output:

Prod Packages
=============

- Install psr/event-dispatcher (1.0.0)
- Change roave/security-advisories (dev-master 3c97c13 => dev-master ac36586)
- Install symfony/deprecation-contracts (v2.1.2)
- Upgrade symfony/event-dispatcher (v2.8.52 => v5.1.2)
- Install symfony/event-dispatcher-contracts (v2.1.2)
- Install symfony/polyfill-php80 (v1.17.1)

Dev Packages
============

- Downgrade phpunit/php-code-coverage (8.0.2 => 7.0.10)
- Downgrade phpunit/php-file-iterator (3.0.2 => 2.0.2)
- Downgrade phpunit/php-text-template (2.0.1 => 1.2.1)
- Downgrade phpunit/php-timer (5.0.0 => 2.1.2)
- Downgrade phpunit/php-token-stream (4.0.2 => 3.1.1)
- Downgrade phpunit/phpunit (9.2.5 => 8.5.8)
- Downgrade sebastian/code-unit-reverse-lookup (2.0.1 => 1.0.1)
- Downgrade sebastian/comparator (4.0.2 => 3.0.2)
- Downgrade sebastian/diff (4.0.1 => 3.0.2)
- Downgrade sebastian/environment (5.1.1 => 4.2.3)
- Downgrade sebastian/exporter (4.0.1 => 3.1.2)
- Downgrade sebastian/global-state (4.0.0 => 3.0.0)
- Downgrade sebastian/object-enumerator (4.0.1 => 3.0.3)
- Downgrade sebastian/object-reflector (2.0.1 => 1.1.1)
- Downgrade sebastian/recursion-context (4.0.1 => 3.0.0)
- Downgrade sebastian/resource-operations (3.0.1 => 2.0.1)
- Downgrade sebastian/type (2.1.0 => 1.1.3)
- Downgrade sebastian/version (3.0.0 => 2.0.1)
- Uninstall phpunit/php-invoker (3.0.1)
- Uninstall sebastian/code-unit (1.0.3)


## JSON (json)

This format will display the changes in a JSON format for parsing by other tools.

Example output:

```json
{
    "packages": {
        "psr\/event-dispatcher": {
            "name": "psr\/event-dispatcher",
            "direct": false,
            "effective": false,
            "operation": "install",
            "version_base": null,
            "version_target": "1.0.0"
        },
        "roave\/security-advisories": {
            "name": "roave\/security-advisories",
            "direct": true,
            "effective": false,
            "operation": "change",
            "version_base": "dev-master 3c97c13",
            "version_target": "dev-master ac36586"
        },
        "symfony\/deprecation-contracts": {
            "name": "symfony\/deprecation-contracts",
            "direct": false,
            "effective": false,
            "operation": "install",
            "version_base": null,
            "version_target": "v2.1.2"
        },
        "symfony\/event-dispatcher": {
            "name": "symfony\/event-dispatcher",
            "direct": true,
            "effective": false,
            "operation": "upgrade",
            "version_base": "v2.8.52",
            "version_target": "v5.1.2"
        },
        "symfony\/event-dispatcher-contracts": {
            "name": "symfony\/event-dispatcher-contracts",
            "direct": false,
            "effective": false,
            "operation": "install",
            "version_base": null,
            "version_target": "v2.1.2"
        },
        "symfony\/polyfill-php80": {
            "name": "symfony\/polyfill-php80",
            "direct": false,
            "effective": false,
            "operation": "install",
            "version_base": null,
            "version_target": "v1.17.1"
        }
    },
    "packages-dev": {
        "phpunit\/php-code-coverage": {
            "name": "phpunit\/php-code-coverage",
            "direct": false,
            "effective": false,
            "operation": "downgrade",
            "version_base": "8.0.2",
            "version_target": "7.0.10"
        },
        "phpunit\/php-file-iterator": {
            "name": "phpunit\/php-file-iterator",
            "direct": false,
            "effective": false,
            "operation": "downgrade",
            "version_base": "3.0.2",
            "version_target": "2.0.2"
        },
        "phpunit\/php-text-template": {
            "name": "phpunit\/php-text-template",
            "direct": false,
            "effective": false,
            "operation": "downgrade",
            "version_base": "2.0.1",
            "version_target": "1.2.1"
        },
        "phpunit\/php-timer": {
            "name": "phpunit\/php-timer",
            "direct": false,
            "effective": false,
            "operation": "downgrade",
            "version_base": "5.0.0",
            "version_target": "2.1.2"
        },
        "phpunit\/php-token-stream": {
            "name": "phpunit\/php-token-stream",
            "direct": false,
            "effective": false,
            "operation": "downgrade",
            "version_base": "4.0.2",
            "version_target": "3.1.1"
        },
        "phpunit\/phpunit": {
            "name": "phpunit\/phpunit",
            "direct": true,
            "effective": false,
            "operation": "downgrade",
            "version_base": "9.2.5",
            "version_target": "8.5.8"
        },
        "sebastian\/code-unit-reverse-lookup": {
            "name": "sebastian\/code-unit-reverse-lookup",
            "direct": false,
            "effective": false,
            "operation": "downgrade",
            "version_base": "2.0.1",
            "version_target": "1.0.1"
        },
        "sebastian\/comparator": {
            "name": "sebastian\/comparator",
            "direct": false,
            "effective": false,
            "operation": "downgrade",
            "version_base": "4.0.2",
            "version_target": "3.0.2"
        },
        "sebastian\/diff": {
            "name": "sebastian\/diff",
            "direct": false,
            "effective": false,
            "operation": "downgrade",
            "version_base": "4.0.1",
            "version_target": "3.0.2"
        },
        "sebastian\/environment": {
            "name": "sebastian\/environment",
            "direct": false,
            "effective": false,
            "operation": "downgrade",
            "version_base": "5.1.1",
            "version_target": "4.2.3"
        },
        "sebastian\/exporter": {
            "name": "sebastian\/exporter",
            "direct": false,
            "effective": false,
            "operation": "downgrade",
            "version_base": "4.0.1",
            "version_target": "3.1.2"
        },
        "sebastian\/global-state": {
            "name": "sebastian\/global-state",
            "direct": false,
            "effective": false,
            "operation": "downgrade",
            "version_base": "4.0.0",
            "version_target": "3.0.0"
        },
        "sebastian\/object-enumerator": {
            "name": "sebastian\/object-enumerator",
            "direct": false,
            "effective": false,
            "operation": "downgrade",
            "version_base": "4.0.1",
            "version_target": "3.0.3"
        },
        "sebastian\/object-reflector": {
            "name": "sebastian\/object-reflector",
            "direct": false,
            "effective": false,
            "operation": "downgrade",
            "version_base": "2.0.1",
            "version_target": "1.1.1"
        },
        "sebastian\/recursion-context": {
            "name": "sebastian\/recursion-context",
            "direct": false,
            "effective": false,
            "operation": "downgrade",
            "version_base": "4.0.1",
            "version_target": "3.0.0"
        },
        "sebastian\/resource-operations": {
            "name": "sebastian\/resource-operations",
            "direct": false,
            "effective": false,
            "operation": "downgrade",
            "version_base": "3.0.1",
            "version_target": "2.0.1"
        },
        "sebastian\/type": {
            "name": "sebastian\/type",
            "direct": false,
            "effective": false,
            "operation": "downgrade",
            "version_base": "2.1.0",
            "version_target": "1.1.3"
        },
        "sebastian\/version": {
            "name": "sebastian\/version",
            "direct": false,
            "effective": false,
            "operation": "downgrade",
            "version_base": "3.0.0",
            "version_target": "2.0.1"
        },
        "phpunit\/php-invoker": {
            "name": "phpunit\/php-invoker",
            "direct": false,
            "effective": false,
            "operation": "remove",
            "version_base": "3.0.1",
            "version_target": null
        },
        "sebastian\/code-unit": {
            "name": "sebastian\/code-unit",
            "direct": false,
            "effective": false,
            "operation": "remove",
            "version_base": "1.0.3",
            "version_target": null
        }
    }
}
```

## CSV (csv)

This format outputs a single CSV table for spreadsheets and other tools. The first row is a header,
and the `section` column tells prod (`prod`) and dev (`dev`) packages apart. The other columns match the JSON keys:

- `name` - package name
- `direct` - `true` if the package is a direct dependency of the project, `false` otherwise
- `effective` - `true` for effective platform requirements listed with `--with-platform` (see [Platform requirements](../README.md#platform-requirements)), `false` otherwise
- `operation` - `install`, `upgrade`, `downgrade`, `change` or `remove`
- `version_base` and `version_target` - versions before and after the change, empty when the package was installed or removed
- `licenses` - comma-separated licenses, only with `--with-licenses`
- `compare` and `link` - compare/release URL and project URL, only with `--with-links`

Fields containing spaces, commas, quotes or line breaks are enclosed in double quotes, and quotes inside them are doubled.
The header row is printed even when there are no changes.

Example output:

```csv
section,name,direct,effective,operation,version_base,version_target
prod,psr/event-dispatcher,false,false,install,,1.0.0
prod,roave/security-advisories,true,false,change,"dev-master 3c97c13","dev-master ac36586"
prod,symfony/deprecation-contracts,false,false,install,,v2.1.2
prod,symfony/event-dispatcher,true,false,upgrade,v2.8.52,v5.1.2
prod,symfony/event-dispatcher-contracts,false,false,install,,v2.1.2
prod,symfony/polyfill-php80,false,false,install,,v1.17.1
dev,phpunit/php-code-coverage,false,false,downgrade,8.0.2,7.0.10
dev,phpunit/php-file-iterator,false,false,downgrade,3.0.2,2.0.2
dev,phpunit/php-text-template,false,false,downgrade,2.0.1,1.2.1
dev,phpunit/php-timer,false,false,downgrade,5.0.0,2.1.2
dev,phpunit/php-token-stream,false,false,downgrade,4.0.2,3.1.1
dev,phpunit/phpunit,true,false,downgrade,9.2.5,8.5.8
dev,sebastian/code-unit-reverse-lookup,false,false,downgrade,2.0.1,1.0.1
dev,sebastian/comparator,false,false,downgrade,4.0.2,3.0.2
dev,sebastian/diff,false,false,downgrade,4.0.1,3.0.2
dev,sebastian/environment,false,false,downgrade,5.1.1,4.2.3
dev,sebastian/exporter,false,false,downgrade,4.0.1,3.1.2
dev,sebastian/global-state,false,false,downgrade,4.0.0,3.0.0
dev,sebastian/object-enumerator,false,false,downgrade,4.0.1,3.0.3
dev,sebastian/object-reflector,false,false,downgrade,2.0.1,1.1.1
dev,sebastian/recursion-context,false,false,downgrade,4.0.1,3.0.0
dev,sebastian/resource-operations,false,false,downgrade,3.0.1,2.0.1
dev,sebastian/type,false,false,downgrade,2.1.0,1.1.3
dev,sebastian/version,false,false,downgrade,3.0.0,2.0.1
dev,phpunit/php-invoker,false,false,remove,3.0.1,
dev,sebastian/code-unit,false,false,remove,1.0.3,
```

## Collapsible GitHub PR description (pr)

This format wraps each section in GitHub-flavored `<details>`/`<summary>` blocks, making it ideal for posting dependency change summaries in pull request descriptions. Each section is collapsed by default and shows the section title with the package count.

Example output:

```
<details>
<summary>Prod Packages (6 packages)</summary>

| Prod Packages                      | Operation | Base               | Target             |
|------------------------------------|-----------|--------------------|--------------------|
| psr/event-dispatcher               | New       | -                  | 1.0.0              |
| roave/security-advisories          | Changed   | dev-master 3c97c13 | dev-master ac36586 |
| symfony/deprecation-contracts      | New       | -                  | v2.1.2             |
| symfony/event-dispatcher           | Upgraded  | v2.8.52            | v5.1.2             |
| symfony/event-dispatcher-contracts | New       | -                  | v2.1.2             |
| symfony/polyfill-php80             | New       | -                  | v1.17.1            |

</details>
```

## GitHub Annotations (github)

This format will display the changes in a format that can be used as GitHub annotation notices.

Example output:

```
::notice title=Prod Packages:: - Install psr/event-dispatcher (1.0.0)%0A - Change roave/security-advisories (dev-master 3c97c13 => dev-master ac36586)%0A - Install symfony/deprecation-contracts (v2.1.2)%0A - Upgrade symfony/event-dispatcher (v2.8.52 => v5.1.2)%0A - Install symfony/event-dispatcher-contracts (v2.1.2)%0A - Install symfony/polyfill-php80 (v1.17.1)
::notice title=Dev Packages:: - Downgrade phpunit/php-code-coverage (8.0.2 => 7.0.10)%0A - Downgrade phpunit/php-file-iterator (3.0.2 => 2.0.2)%0A - Downgrade phpunit/php-text-template (2.0.1 => 1.2.1)%0A - Downgrade phpunit/php-timer (5.0.0 => 2.1.2)%0A - Downgrade phpunit/php-token-stream (4.0.2 => 3.1.1)%0A - Downgrade phpunit/phpunit (9.2.5 => 8.5.8)%0A - Downgrade sebastian/code-unit-reverse-lookup (2.0.1 => 1.0.1)%0A - Downgrade sebastian/comparator (4.0.2 => 3.0.2)%0A - Downgrade sebastian/diff (4.0.1 => 3.0.2)%0A - Downgrade sebastian/environment (5.1.1 => 4.2.3)%0A - Downgrade sebastian/exporter (4.0.1 => 3.1.2)%0A - Downgrade sebastian/global-state (4.0.0 => 3.0.0)%0A - Downgrade sebastian/object-enumerator (4.0.1 => 3.0.3)%0A - Downgrade sebastian/object-reflector (2.0.1 => 1.1.1)%0A - Downgrade sebastian/recursion-context (4.0.1 => 3.0.0)%0A - Downgrade sebastian/resource-operations (3.0.1 => 2.0.1)%0A - Downgrade sebastian/type (2.1.0 => 1.1.3)%0A - Downgrade sebastian/version (3.0.0 => 2.0.1)%0A - Uninstall phpunit/php-invoker (3.0.1)%0A - Uninstall sebastian/code-unit (1.0.3)
```

# Contributing

All formatters are implemented as separate classes in the `IonBazan\ComposerDiff\Formatter` namespace 
and must implement the `IonBazan\ComposerDiff\Formatter\FormatterInterface` interface.

If you would like to create a new formatter, create a new class in the `Formatter` namespace and register it in `FormatterContainer`.
