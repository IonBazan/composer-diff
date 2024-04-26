# Output formatters

There are currently four output formats available:

- `mdtable` - Markdown table (default)
- `mdlist` - Markdown list
- `json` - JSON
- `github` - GitHub Annotations

You can select the output format using the `--format` (`-f`) option.

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
            "operation": "install",
            "version_base": null,
            "version_target": "1.0.0"
        },
        "roave\/security-advisories": {
            "name": "roave\/security-advisories",
            "operation": "change",
            "version_base": "dev-master 3c97c13",
            "version_target": "dev-master ac36586"
        },
        "symfony\/deprecation-contracts": {
            "name": "symfony\/deprecation-contracts",
            "operation": "install",
            "version_base": null,
            "version_target": "v2.1.2"
        },
        "symfony\/event-dispatcher": {
            "name": "symfony\/event-dispatcher",
            "operation": "upgrade",
            "version_base": "v2.8.52",
            "version_target": "v5.1.2"
        },
        "symfony\/event-dispatcher-contracts": {
            "name": "symfony\/event-dispatcher-contracts",
            "operation": "install",
            "version_base": null,
            "version_target": "v2.1.2"
        },
        "symfony\/polyfill-php80": {
            "name": "symfony\/polyfill-php80",
            "operation": "install",
            "version_base": null,
            "version_target": "v1.17.1"
        }
    },
    "packages-dev": {
        "phpunit\/php-code-coverage": {
            "name": "phpunit\/php-code-coverage",
            "operation": "downgrade",
            "version_base": "8.0.2",
            "version_target": "7.0.10"
        },
        "phpunit\/php-file-iterator": {
            "name": "phpunit\/php-file-iterator",
            "operation": "downgrade",
            "version_base": "3.0.2",
            "version_target": "2.0.2"
        },
        "phpunit\/php-text-template": {
            "name": "phpunit\/php-text-template",
            "operation": "downgrade",
            "version_base": "2.0.1",
            "version_target": "1.2.1"
        },
        "phpunit\/php-timer": {
            "name": "phpunit\/php-timer",
            "operation": "downgrade",
            "version_base": "5.0.0",
            "version_target": "2.1.2"
        },
        "phpunit\/php-token-stream": {
            "name": "phpunit\/php-token-stream",
            "operation": "downgrade",
            "version_base": "4.0.2",
            "version_target": "3.1.1"
        },
        "phpunit\/phpunit": {
            "name": "phpunit\/phpunit",
            "operation": "downgrade",
            "version_base": "9.2.5",
            "version_target": "8.5.8"
        },
        "sebastian\/code-unit-reverse-lookup": {
            "name": "sebastian\/code-unit-reverse-lookup",
            "operation": "downgrade",
            "version_base": "2.0.1",
            "version_target": "1.0.1"
        },
        "sebastian\/comparator": {
            "name": "sebastian\/comparator",
            "operation": "downgrade",
            "version_base": "4.0.2",
            "version_target": "3.0.2"
        },
        "sebastian\/diff": {
            "name": "sebastian\/diff",
            "operation": "downgrade",
            "version_base": "4.0.1",
            "version_target": "3.0.2"
        },
        "sebastian\/environment": {
            "name": "sebastian\/environment",
            "operation": "downgrade",
            "version_base": "5.1.1",
            "version_target": "4.2.3"
        },
        "sebastian\/exporter": {
            "name": "sebastian\/exporter",
            "operation": "downgrade",
            "version_base": "4.0.1",
            "version_target": "3.1.2"
        },
        "sebastian\/global-state": {
            "name": "sebastian\/global-state",
            "operation": "downgrade",
            "version_base": "4.0.0",
            "version_target": "3.0.0"
        },
        "sebastian\/object-enumerator": {
            "name": "sebastian\/object-enumerator",
            "operation": "downgrade",
            "version_base": "4.0.1",
            "version_target": "3.0.3"
        },
        "sebastian\/object-reflector": {
            "name": "sebastian\/object-reflector",
            "operation": "downgrade",
            "version_base": "2.0.1",
            "version_target": "1.1.1"
        },
        "sebastian\/recursion-context": {
            "name": "sebastian\/recursion-context",
            "operation": "downgrade",
            "version_base": "4.0.1",
            "version_target": "3.0.0"
        },
        "sebastian\/resource-operations": {
            "name": "sebastian\/resource-operations",
            "operation": "downgrade",
            "version_base": "3.0.1",
            "version_target": "2.0.1"
        },
        "sebastian\/type": {
            "name": "sebastian\/type",
            "operation": "downgrade",
            "version_base": "2.1.0",
            "version_target": "1.1.3"
        },
        "sebastian\/version": {
            "name": "sebastian\/version",
            "operation": "downgrade",
            "version_base": "3.0.0",
            "version_target": "2.0.1"
        },
        "phpunit\/php-invoker": {
            "name": "phpunit\/php-invoker",
            "operation": "remove",
            "version_base": "3.0.1",
            "version_target": null
        },
        "sebastian\/code-unit": {
            "name": "sebastian\/code-unit",
            "operation": "remove",
            "version_base": "1.0.3",
            "version_target": null
        }
    }
}
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
