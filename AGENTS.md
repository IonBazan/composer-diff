# AGENTS.md

Guidance for AI coding agents working in this repository. Humans and agents alike
must follow the full [Contributing guidelines](docs/CONTRIBUTING.md), including the
**AI Tools** section — read it before opening a PR.

## Ground rules for AI-assisted changes

- You are responsible for every line you submit. If you can't explain why a line is
  there and why it's correct, don't submit it.
- Do not paste raw AI output into code, issues, PRs, or comments. Rewrite drafted
  text in your own words.
- Strip AI-generated footers, co-author trailers, and "Generated with…" signatures
  from commits and PR descriptions before submitting.
- Automated, unreviewed submissions are treated as spam.
- All new features target the `main` branch.

## Compatibility constraints

The code must run on **PHP 7.2 through 8.5 and newer** — see the CI matrix in
`.github/workflows/test.yml`. Write to the lowest supported version:

- No PHP 8.0+ syntax in shipped code: no constructor property promotion, `match`,
  named arguments, enums, readonly properties, nullsafe `?->`, `never` return type,
  first-class callable syntax, or trailing commas in parameter lists.
- Prefer full type declarations (params, return types, `void`) as the existing
  `src/` code does; keep property types in docblocks (`@var`), not native syntax.
- Short array syntax (`[]`) and null coalescing (`??`) are fine.
- Supported dependency ranges: `composer/composer` `^2.0`,
  `composer-plugin-api` `^2.0`, `symfony/console` `^5.4 || ^6.0 || ^7.0 || ^8.0`.
  Don't rely on APIs missing from the lowest supported versions, and guard
  version-specific behavior at runtime where the existing code already does.

## Quality gates (all enforced in CI)

- **Tests:** `vendor/bin/simple-phpunit` — add tests for every change.
- **Coverage:** 100% line coverage.
- **Mutation testing:** 100% MSI and covered MSI (`vendor/bin/infection`).
- **Static analysis:** PHPStan level 7 over `src/` and `tests/` (`vendor/bin/phpstan`).
- **Coding style:** enforced by [StyleCI](https://styleci.io/); keep the existing
  formatting and import ordering.
- **Autoload:** PSR-4 mapping must be strict (`composer dump-autoload --strict-psr`).

## Local workflow

```shell
composer install
vendor/bin/simple-phpunit
vendor/bin/phpstan
```

To exercise a change against a real project, run this repo's `./composer-diff`
binary from inside that project's directory (see
[Testing against a real project](docs/CONTRIBUTING.md#testing-against-a-real-project)).

## Layout

- `src/` — plugin and command code (`IonBazan\ComposerDiff\`).
- `tests/` — PHPUnit suite (`IonBazan\ComposerDiff\Tests\`).
- `docs/` — [formatters](docs/formatters.md), [URL generators](docs/url-generators.md),
  [contributing](docs/CONTRIBUTING.md).
