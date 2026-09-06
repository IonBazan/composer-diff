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
- New features target the `main` branch. This `1.x` branch is maintenance-only —
  bug fixes and compatibility work.

## Compatibility constraints (this branch)

The code must run on **PHP 5.3.2 through 8.5 and newer** (see the CI matrix in
`.github/workflows/test.yml`). Write to the lowest supported version:

- Use `array()`, never the short `[]` syntax.
- No scalar/return type declarations, nullable types, union types, or typed
  properties in shipped classes. The Symfony 7+ typed-signature shim lives in
  `src/Command/BaseTypedCommand.php` and is selected at runtime via `class_alias`
  on `PHP_VERSION_ID` — follow that existing pattern instead of adding types.
- No null coalescing (`??`), spaceship (`<=>`), arrow functions, first-class
  callable syntax, `list()`-in-`[]` destructuring, constant visibility, or
  trailing commas in function calls/params.
- Guard any PHP-version- or Composer-version-specific behavior at runtime, as the
  existing code does for Composer 1.x vs 2.x.

Dependencies also span wide ranges: `composer/composer` `^1.10.27 || ^2.0` and
`symfony/console` `^2.3` through `^8.0`. Don't rely on APIs missing from the
lowest supported versions.

## Quality gates (all enforced in CI)

- **Tests:** `vendor/bin/simple-phpunit` — add tests for every change.
- **Coverage:** 100% line coverage.
- **Mutation testing:** 100% MSI and covered MSI (`vendor/bin/infection`).
- **Static analysis:** PHPStan level 7 over `src/` (`vendor/bin/phpstan`).
- **Coding style:** enforced by [StyleCI](https://styleci.io/); keep the existing
  formatting and import ordering.

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
